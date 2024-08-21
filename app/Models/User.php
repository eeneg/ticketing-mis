<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'number',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Request::class, 'assignee')
            ->using(Assignee::class)
            ->withPivot(['response', 'responded_at']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (
            $panel->getID() === UserRole::USER->value ||
            $panel->getID() === Auth::user()->role->value
        ) {
            return true;
        }

        return false;
    }
}
