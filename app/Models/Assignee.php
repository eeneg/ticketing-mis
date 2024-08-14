<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Assignee extends Pivot
{
    use HasFactory, HasUlids;

    protected $table = 'assignees';

    protected $fillable = [
        'request_id', 'user_id', 'assigner_id', 'response', 'reponded_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function request()
    {
        return $this->belongsToMany(Request::class);
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
}
