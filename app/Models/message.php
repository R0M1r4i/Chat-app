<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'conversation_id', // Si estás usando la tabla de conversaciones
    ];

    // Relación con el remitente (sender)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relación con el destinatario (receiver)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Relación con la conversación (opcional)
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
