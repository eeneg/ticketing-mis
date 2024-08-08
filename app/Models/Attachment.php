<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Attachment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'file', 'attachable_type', 'attachable_id',
    ];

    public function request(): MorphToMany
    {
        return $this->morphedByMany(Request::class, 'attachable');
    }

    public function action(): MorphToMany
    {
        return $this->morphedByMany(Action::class, 'attachable');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
