<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'category_id', 'subcategory_id', 'remarks', 'priority', 'difficulty', 'target_date', 'target_time', 'availability_from', 'availability_to',
    ];
}
