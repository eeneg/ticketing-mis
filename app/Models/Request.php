<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class Request extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'subject',
        'office_id',
        'category_id',
        'subcategory_id',
        'requestor_id',
        'remarks',
        'priority',
        'difficulty',
        'target_date',
        'target_time',
        'availability_from',
        'availability_to',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $request) => $request->purge());

        static::saving(function (self $request) {
            $request->tags()->sync(
                $request->tags()
                    ->where(function (Builder $query) use ($request) {
                        $query->orWhere(fn ($query) => $query->where('taggable_type', Subcategory::class)->where('taggable_id', $request->subcategory_id));

                        $query->orWhere(fn ($query) => $query->where('taggable_type', Category::class)->where('taggable_id', $request->category_id));
                    })
                    ->pluck('tags.id')
            );
        });
    }

    public function currentUserAssignee(): HasOne
    {
        return $this->hasOne(Assignee::class)
            ->ofMany(['id' => 'max'], fn ($query) => $query->where('assignees.user_id', Auth::id()));
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignees')
            ->using(Assignee::class);

    }

    public function action(): HasOne
    {
        return $this->hasOne(Action::class)
            ->ofMany(['id' => 'max'], function ($query) {
                $query->whereIn('status', [
                    RequestStatus::APPROVED,
                    RequestStatus::DECLINED,
                    RequestStatus::PUBLISHED,
                    RequestStatus::COMPLETED,
                    RequestStatus::CANCELLED,
                    RequestStatus::STARTED,
                    RequestStatus::SUSPENDED,
                    RequestStatus::RETRACTED,
                    RequestStatus::COMPLIED,
                    RequestStatus::RESOLVED,
                    RequestStatus::VERIFIED,
                    RequestStatus::DENIED,
                    RequestStatus::EXTENDED,
                ]);
            });
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function attachments(): HasManyThrough
    {
        $through = $this->newRelatedThroughInstance(Action::class);

        $firstKey = $this->getForeignKey();

        $secondKey = 'attachable_id';

        return $this->hasManyAttachmentsThroughActions(
            $this->newRelatedInstance(Attachment::class)->newQuery(),
            $this,
            $through,
            $firstKey,
            $secondKey,
            $this->getKeyName(),
            $through->getKeyName(),
        );
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user')
            ->using(User::class)
            ->withPivot(['response', 'responded_at']);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'labels')
            ->using(Label::class)
            ->orderBy('tags.name');
    }

    public function sanitize(): void
    {
        $this->attachments()->lazyById()->each(fn (Attachment $attachment) => $attachment->sanitize());
    }

    public function purge(): void
    {
        $this->files()->each(fn ($file) => Storage::delete($file));

        $this->attachments()->delete();
    }

    public function files(): LazyCollection
    {
        return LazyCollection::make(function () {
            $directory = 'attachments';

            $handle = opendir(Storage::path('public/'.$directory));

            $actions = $this->actions->pluck('id');

            if ($handle) {
                while (($file = readdir($handle)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    @[$type, $id] = explode('-', $file);

                    if ($type === 'request' && $id !== $this->id || $type === 'action' && $actions->doesntContain($id)) {
                        continue;
                    }

                    yield "public/$directory/$file";
                }

                closedir($handle);
            }
        });
    }

    public function hasManyAttachmentsThroughActions(
        Builder $query,
        Model $farParent,
        Model $throughParent,
        $firstKey,
        $secondKey,
        $localKey,
        $secondLocalKey
    ): HasManyThrough {
        return new class($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey) extends HasManyThrough
        {
            public function addEagerConstraints(array $models)
            {
                $whereIn = $this->whereInMethod($this->farParent, $this->localKey);

                $keys = $this->getKeys($models, $this->localKey);

                $this->whereInEager(
                    $whereIn,
                    $this->getQualifiedFirstKeyName(),
                    $keys
                );

                $this->query->orWhere(function (Builder $query) use ($keys) {
                    $query->where('attachable_type', $this->farParent->getMorphClass())
                        ->whereIn('attachable_id', $keys);
                });

                $keys = implode(', ', array_map(fn ($id) => "'".$id."'", $keys));

                $this->query->select('attachments.*');

                $this->query->addSelect($this->raw(<<<SQL
                    CASE
                        WHEN attachments.attachable_type = 'App\Models\Request'
                            AND attachments.attachable_id IN ($keys)
                        THEN attachments.attachable_id
                        ELSE actions.request_id
                    END AS laravel_direct_key
                SQL));
            }

            public function addConstraints()
            {
                $localValue = $this->farParent[$this->localKey];

                $this->performJoin();

                if (self::$constraints) {
                    $this->query->where($this->getQualifiedFirstKeyName(), '=', $localValue);

                    $this->query->orWhere(function (Builder $query) {
                        $query->where('attachable_type', $this->farParent->getMorphClass())
                            ->where('attachable_id', $this->farParent->getKey());
                    });
                }
            }

            protected function performJoin(?Builder $query = null)
            {
                $query = $query ?: $this->query;

                $farKey = $this->getQualifiedFarKeyName();

                $query->leftJoin($this->throughParent->getTable(), function ($join) use ($farKey) {
                    $join->on($this->getQualifiedParentKeyName(), '=', $farKey);

                    $join->where('attachable_type', $this->throughParent->getMorphClass());
                });

                if ($this->throughParentSoftDeletes()) {
                    $query->withGlobalScope('SoftDeletableHasManyThrough', function ($query) {
                        $query->whereNull($this->throughParent->getQualifiedDeletedAtColumn());
                    });
                }
            }

            protected function buildDictionary(Collection $results)
            {
                $dictionary = [];

                foreach ($results as $result) {
                    $dictionary[$result->laravel_through_key ?? $result->laravel_direct_key][] = $result;
                }

                return $dictionary;
            }
        };
    }
}
