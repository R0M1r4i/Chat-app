@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Chat con {{ $user->name }}</h2>

        <div id="chatMessages">
            <!-- Mensajes cargados por AJAX -->
        </div>

        <div class="input-group mt-3">
            <input type="text" id="messageInput" class="form-control" placeholder="Escribe tu mensaje...">
            <button class="btn btn-primary" onclick="sendMessage({{ $conversation->id }})">Enviar</button>
        </div>
    </div>

    <script>
        function sendMessage(conversationId) {
            let message = document.getElementById("messageInput").value;

            if (message.trim() !== "") {
                fetch("/api/messages", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message: message
                    })
                }).then(response => response.json())
                    .then(data => {
                        alert("Mensaje enviado: " + data.message);
                        document.getElementById("messageInput").value = "";
                    });
            }
        }
    </script>
@endsection

