<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user1_id',
        'user2_id',
    ];

    // Relación con el primer usuario (user1)
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    // Relación con el segundo usuario (user2)
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    // Relación con los mensajes de la conversación
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
