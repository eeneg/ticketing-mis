<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Request extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
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
        'published_at',
    ];

    public function currentUserAssignee(): HasOne
    {
        return $this->hasOne(Assignee::class)
            ->ofMany(['id' => 'max'], fn ($query) => $query->where('assignees.user_id', Auth::id()));
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(Assignee::class);
    }

    public function action(): HasOne
    {
        return $this->hasOne(Action::class)
            ->ofMany(['id' => 'max'], function ($query) {
                $query->whereIn('status', [
                    RequestStatus::APPROVED,
                    RequestStatus::DECLINED,
                    RequestStatus::COMPLETED,
                    RequestStatus::CANCELLED,
                    RequestStatus::STARTED,
                    RequestStatus::SUSPENDED,
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

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Attachment::class);
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
        return $this->belongsToMany(Tag::class, 'marks')
            ->using(Mark::class);
    }
}
