<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Action extends Model
{
    use HasFactory, HasUlids;

    protected $casts = [
        'status' => RequestStatus::class,
    ];

    protected $fillable = [
        'request_id', 'user_id', 'status', 'remarks', 'time',
    ];

    public function request()
    {
        return $this->belongsToMany(Request::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphToMany
    {
        return $this->morphedByMany(Attachment::class, 'taggable');
    }
}
