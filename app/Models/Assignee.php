<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignee extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'request_id', 'user_id', 'assigner_id', 'response', 'reponded_at',
    ];
}
