<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'number',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function assignments()
    {
        return $this->belongsToMany(Request::class, 'assignee')
            ->using(Assignee::class)
            ->withPivot(['response', 'responded_at']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getID() == UserRole::USER->value) {
            return true;
        }

        if (Auth::user()->role->value != $panel->getID()) {
            return false;
        }

        return true;
    }


}
