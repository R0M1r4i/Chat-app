<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messageread extends Model
{
    use HasFactory;


    protected $fillable = [
        'message_id',
        'reader_id',
        'read_at',
    ];

    // Relación con el mensaje
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    // Relación con el usuario que leyó el mensaje
    public function reader()
    {
        return $this->belongsTo(User::class, 'reader_id');
    }
}
