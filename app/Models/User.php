<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function createdIncidents()
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    public function assignedIncidents()
    {
        return $this->hasMany(Incident::class, 'technician_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function isTechnician(): bool
    {
        return $this->role === 'tecnico';
    }
}
