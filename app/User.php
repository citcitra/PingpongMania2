<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'admin_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relasi User dengan Admin penanggung jawab
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Daftar User yang menjadi tanggung jawab Admin
    public function managedUsers()
    {
        return $this->hasMany(User::class, 'admin_id');
    }
}
