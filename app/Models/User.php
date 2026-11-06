<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'username', 'email', 'password', 'fullname', 'phone_number', 'photo', 'role_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === Role::ADMIN;
    }

    public function isPic(): bool
    {
        return (int) $this->role_id === Role::PIC;
    }

    public function isGuru(): bool
    {
        return (int) $this->role_id === Role::GURU || (int) $this->role_id === Role::ORGANISASI;
    }

    public function isOrganisasi(): bool
    {
        return (int) $this->role_id === Role::ORGANISASI;
    }

    public function isWaka(): bool
    {
        return (int) $this->role_id === Role::WAKA;
    }

    public function canApprove(): bool
    {
        return $this->isAdmin() || $this->isWaka();
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'pic_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', 0);
    }
}
