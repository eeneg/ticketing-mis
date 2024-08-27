<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class Attachment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'files',
        'paths',
        'attachable_type',
        'attachable_id',
    ];

    protected $casts = [
        'files' => 'collection',
        'paths' => 'collection',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $attachment) => $attachment->purge());
    }

    public function request(): MorphOne
    {
        return $this->morphOne(Request::class, 'attachable');
    }

    public function action(): MorphOne
    {
        return $this->morphOne(Action::class, 'attachable');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function empty(): Attribute
    {
        return Attribute::make(function (): bool {
            return $this->paths->isEmpty();
        })->shouldCache();
    }

    public function sanitize(): void
    {
        $this->retrieve()->diff($this->paths->map(fn ($path) => str($path)->prepend('public/')))->each(fn ($file) => Storage::delete($file));

        $this->update([
            'paths' => $paths = $this->retrieve()->map(fn ($path) => (string) str($path)->replace('public/', '')),
            'files' => $this->files->filter(fn ($name, $key) => in_array($key, $paths->toArray())),
        ]);
    }

    public function purge(): void
    {
        $this->retrieve()->each(fn ($file) => Storage::delete($file));
    }

    public function retrieve(): LazyCollection
    {
        return LazyCollection::make(function () {
            $directory = 'attachments';

            $handle = opendir(Storage::path('public/'.$directory));

            if ($handle) {
                while (($file = readdir($handle)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    if (
                        ! str_starts_with($file, mb_strtolower(class_basename($this->attachable_type))."-$this->attachable_id")
                    ) {
                        continue;
                    }

                    yield "public/$directory/$file";
                }

                closedir($handle);
            }
        });
    }
}
