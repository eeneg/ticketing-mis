<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'taggable_type',
        'taggable_id',
    ];

    public function subcategory(): MorphToMany
    {
        return $this->morhpedByMany(Subcategory::class, 'taggable');
    }

    public function category(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'taggable');
    }

    public function requests(): BelongsToMany
    {
        return $this->belongsToMany(Request::class, 'labels')
            ->using(Label::class)
            ->orderBy('tags.name');
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function name(): Attribute
    {
        return Attribute::make(
            fn (string $name) => preg_replace('/\s+/', ' ', mb_strtolower(trim($name))),
            fn (string $name) => preg_replace('/\s+/', ' ', mb_strtolower(trim($name))),
        );
    }
}
