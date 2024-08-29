<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Office extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'acronym',
        'address',
        'building',
        'room',
        'logo',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function subcategories(): HasManyThrough
    {
        return $this->hasManyThrough(Subcategory::class, Category::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }


}
