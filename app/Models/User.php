<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relación con los mensajes enviados
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Relación con los mensajes recibidos
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
