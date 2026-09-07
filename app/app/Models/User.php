<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_LIBRARIAN = 'librarian';
    public const ROLE_MEMBER = 'member';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_LIBRARIAN,
        self::ROLE_MEMBER,
    ];

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
    ];

    /**
     * The loans the member has created.
     */
    public function loans()
    {
        return $this->hasMany(BookLoan::class);
    }

    /**
     * Loans this user processed as staff (check-outs / check-ins).
     */
    public function handledLoans()
    {
        return $this->hasMany(BookLoan::class, 'handled_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isLibrarian(): bool
    {
        return $this->role === self::ROLE_LIBRARIAN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isLibrarian();
    }

    public function canManageCatalog(): bool
    {
        return $this->isStaff();
    }

    public function roleLabel(): string
    {
        return ucfirst($this->role);
    }
}
