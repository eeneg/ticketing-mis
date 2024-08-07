<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory, HasUlids;
    protected $fillable =[
        'path', 'attachable_type', 'attachable_id',
    ];
}
