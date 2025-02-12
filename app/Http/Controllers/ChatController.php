<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Muestra la lista de usuarios
    public function index()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('chats.index', compact('users'));
    }

    // Obtiene los mensajes de un usuario específico
    public function getMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', Auth::id())->where('receiver_id', $userId);
        })
            ->orWhere(function ($query) use ($userId) {
                $query->where('sender_id', $userId)->where('receiver_id', Auth::id());
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('H:i'),
                ];
            });

        return response()->json($messages);
    }



    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $senderId = Auth::id();
        $receiverId = $request->receiver_id;

        // Buscar o crear la conversación entre los dos usuarios
        $conversation = \App\Models\Conversation::firstOrCreate([
            'user1_id' => min($senderId, $receiverId),
            'user2_id' => max($senderId, $receiverId),
        ]);

        // Crear el mensaje y asignar conversation_id correctamente
        $message = \App\Models\Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'conversation_id' => $conversation->id, // ✅ Esto evita que sea NULL
            'message' => $request->message,
        ]);

        // Enviar evento a Pusher
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message->message,
            'conversation_id' => $conversation->id, // ✅ Enviar este dato al frontend
            'status' => 'success'
        ]);
    }

}
