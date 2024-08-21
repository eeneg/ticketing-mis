<?php

namespace App\Models;

use App\Enums\UserAssignmentResponse;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Assignee extends Pivot
{
    use HasFactory, HasUlids;

    protected $table = 'assignees';

    protected $fillable = [
        'request_id',
        'user_id',
        'assigner_id',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'response' => UserAssignmentResponse::class,
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function request(): BelongsToMany
    {
        return $this->belongsToMany(Request::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
}
