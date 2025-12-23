<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $prenom
 * @property string $nom
 * @property string $pays
 * @property string $contact
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $token
 * @property \Illuminate\Support\Carbon $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration wherePays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingRegistration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
