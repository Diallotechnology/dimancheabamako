<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PendingRegistration extends Model
{
    /** @use HasFactory<\Database\Factories\PendingRegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'token',
        'expires_at',
        'role',
        'prenom',
        'nom',
        'pays',
        'contact',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
