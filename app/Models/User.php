<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\RoleEnum;
use App\Helper\DateFormat;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use DateFormat, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'etat',
        'change_password',
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
        'role' => RoleEnum::class,
    ];

    /**
     * Check if the user has the invite role.
     */
    public function isClient(): bool
    {
        return $this->role === RoleEnum::CUSTOMER;
    }

    /**
     * Check if the user has the invite role.
     */
    public function isSecretaire(): bool
    {
        return $this->role === RoleEnum::SECRTETAIRE;
    }

    /**
     * Check if the user has the admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === RoleEnum::ADMIN;
    }
}
