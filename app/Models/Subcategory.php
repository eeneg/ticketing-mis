<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Subcategory extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name', 'category_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morthToMany(Tag::class, 'taggable');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
