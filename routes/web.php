<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Ruta protegida para el chat
Route::get('/chats', [AuthController::class, 'chats'])->name('chats.index')->middleware('auth');


use App\Http\Controllers\ChatController;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

Route::get('/chat/get-conversation/{user}', function ($userId) {
    $conversation = Conversation::where(function ($query) use ($userId) {
        $query->where('user1_id', Auth::id())->where('user2_id', $userId);
    })->orWhere(function ($query) use ($userId) {
        $query->where('user1_id', $userId)->where('user2_id', Auth::id());
    })->first();

    return response()->json(['conversation_id' => $conversation ? $conversation->id : null]);
});




Route::middleware(['auth'])->group(function () {
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chat/messages/{user}', [ChatController::class, 'getMessages']);
    Route::post('/chat/send-message', [ChatController::class, 'sendMessage']);
});

