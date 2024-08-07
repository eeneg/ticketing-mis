<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Actions extends Model
{
    use HasFactory, HasUlids;
    protected $fillable =[
        'request_id', 'user_id', 'status', 'remarks', 'time'
    ];
}
