<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Request extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'category_id', 'office_id', 'subcategory_id', 'requestor_id', 'remarks', 'priority', 'difficulty', 'target_date', 'target_time', 'availability_from', 'availability_to', 'published_at',
    ];

    public function currentUserAssignee()
    {
        return $this->hasOne(Assignee::class)
            ->ofMany(['id' => 'max'], fn ($query) => $query->where('assignees.user_id', Auth::id()));
    }

    public function assignees()
    {
        return $this->hasMany(Assignee::class);
    }

    public function action()
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

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function requestor()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Attachment::class);
    }

    public function assignments()
    {
        return $this->belongsToMany(User::class, 'user')
            ->using(User::class)
            ->withPivot(['response', 'responded_at']);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
}
