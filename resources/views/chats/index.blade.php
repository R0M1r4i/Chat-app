<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <!--  This file has been downloaded from bootdey.com @bootdey on twitter -->
    <!--  All snippets are MIT license http://bootdey.com/license -->
    <title>white chat - Bootdey.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>




    <style type="text/css">
        body{margin-top:20px;}

        .chat-online {
            color: #34ce57
        }

        .chat-offline {
            color: #e4606d
        }

        .chat-messages {
            display: flex;
            flex-direction: column;
            max-height: 800px;
            overflow-y: scroll
        }

        .chat-message-left,
        .chat-message-right {
            display: flex;
            flex-shrink: 0
        }

        .chat-message-left {
            margin-right: auto
        }

        .chat-message-right {
            flex-direction: row-reverse;
            margin-left: auto
        }
        .py-3 {
            padding-top: 1rem!important;
            padding-bottom: 1rem!important;
        }
        .px-4 {
            padding-right: 1.5rem!important;
            padding-left: 1.5rem!important;
        }
        .flex-grow-0 {
            flex-grow: 0!important;
        }
        .border-top {
            border-top: 1px solid #dee2e6!important;
        }
    </style>
</head>
<body>
@extends('layouts.app')

@section('content')

    <main class="content">
        <div class="container p-0">
            <h1 class="h3 mb-3">Messages</h1>

            <div class="card">
                <div class="row g-0">
                    <!-- Sidebar de Usuarios -->
                    <div class="col-12 col-md-4 col-lg-3 border-right">
                        <div class="px-4 py-3 border-bottom">
                            <input type="text" id="searchUser" class="form-control" placeholder="Search by email..." onkeyup="searchUser()">
                        </div>

                        <div id="userList" class="list-group">
                            @foreach($users as $user)
                                <a href="javascript:void(0);" class="list-group-item list-group-item-action border-0" onclick="loadChat({{ $user->id }}, '{{ $user->name }}')">
                                    <div class="d-flex align-items-start">

                                        <div class="ml-3">
                                            <strong>{{ $user->name }}</strong>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Área del Chat -->
                    <div class="col-12 col-md-8 col-lg-9">
                        <div class="p-3 border-bottom d-flex align-items-center">
                            <strong id="chatTitle">Select a chat</strong>
                        </div>

                        <div class="chat-messages p-4" id="chatMessages">
                            <p class="text-muted text-center">Select a user to start chatting.</p>
                        </div>

                        <!-- Formulario para enviar mensajes -->
                        <div class="border-top p-3">
                            <div class="input-group">
                                <input type="text" id="messageInput" class="form-control" placeholder="Type your message" disabled>
                                <button class="btn btn-primary" id="sendMessageBtn" onclick="sendMessage()" disabled>Send</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>



@endsection
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<script>
    import Pusher from "pusher-js";

    const pusher = new Pusher("9917e71c9e2e09a1f216", {
        cluster: "us2",
        encrypted: true,
        forceTLS: true,
        disableStats: true, // ✅ Reduce el tráfico innecesario de estadísticas
        enabledTransports: ["ws", "wss"] // ✅ Evita que Pusher intente conexiones HTTP fallback
    });

</script>

<script>



    let selectedUserId = null;

    // Buscar usuario por correo
    function searchUser() {
        let input = document.getElementById('searchUser').value.toLowerCase();
        let users = document.querySelectorAll("#userList a");

        users.forEach(user => {
            let email = user.querySelector(".small").innerText.toLowerCase();
            user.style.display = email.includes(input) ? "" : "none";
        });
    }

    function loadChat(userId, userName) {
        selectedUserId = userId;
        document.getElementById("chatTitle").innerText = userName;
        document.getElementById("messageInput").disabled = false;
        document.getElementById("sendMessageBtn").disabled = false;

        fetch(`/chat/messages/${userId}`)
            .then(response => response.json())
            .then(data => {
                renderMessages(data);

                let conversationId = data.length > 0 ? data[0].conversation_id : null;

                if (conversationId) {
                    console.log("🔗 Suscribiéndose a: chat." + conversationId);
                    subscribeToChat(conversationId);
                } else {
                    console.warn("⚠️ No se pudo obtener el conversation_id. Verificando en servidor...");
                    checkConversationId(userId);
                }
            })
            .catch(error => console.error("Error al cargar mensajes:", error));
    }

    // Función extra para intentar obtener conversation_id si no está en los mensajes
    function checkConversationId(userId) {
        fetch(`/chat/get-conversation/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.conversation_id) {
                    console.log("✅ Conversación encontrada: chat." + data.conversation_id);
                    subscribeToChat(data.conversation_id);
                } else {
                    console.warn("❌ No se encontró una conversación para este usuario.");
                }
            })
            .catch(error => console.error("Error al verificar conversación:", error));
    }

    // Renderizar los mensajes en la pantalla
    function renderMessages(messages) {
        let chatMessages = document.getElementById("chatMessages");
        chatMessages.innerHTML = ""; // Limpiar el chat antes de mostrar nuevos mensajes

        messages.forEach(msg => {
            let messageClass = msg.sender_id == {{ auth()->id() }} ? 'chat-message-right' : 'chat-message-left';

            chatMessages.innerHTML += `
            <div class="${messageClass} pb-4">
                <div>

                    <div class="text-muted small text-nowrap mt-2">${msg.created_at}</div>
                </div>
                <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                    <div class="font-weight-bold mb-1">${msg.sender_name}</div>
                    ${msg.message}
                </div>
            </div>
        `;
        });

        chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll al último mensaje
    }

    // Enviar mensaje al servidor
    function sendMessage() {
        let message = document.getElementById("messageInput").value.trim();

        if (message === "" || selectedUserId === null) {
            alert("Please enter a message.");
            return;
        }

        fetch("/chat/send-message", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({
                receiver_id: selectedUserId,
                message: message
            })
        })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP status ${response.status}`);
                return response.json();
            })
            .then(data => {
                document.getElementById("messageInput").value = ""; // Limpiar el input
                renderNewMessage({
                    sender_id: {{ auth()->id() }},
                    message: message,
                    created_at: new Date().toLocaleTimeString()
                });
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error sending message. Check the console for details.");
            });
    }



    // 🔹 Inicializar Pusher
    Pusher.logToConsole = true; // ✅ Esto debe mostrar eventos en la consola del navegador

    const pusher = new Pusher("9917e71c9e2e09a1f216", {
        cluster: "us2",
        encrypted: true,
        forceTLS: true
    });

    function subscribeToChat(conversationId) {
        if (!conversationId) {
            console.error("❌ No se pudo suscribir porque conversationId es null.");
            return;
        }

        console.log("🔗 Suscribiéndose a: chat." + conversationId);

        let channel = pusher.subscribe(`chat.${conversationId}`);

        channel.bind("App\\Events\\MessageSent", function (data) {
            console.log("📩 Nuevo mensaje recibido en chat." + conversationId, data);
            if (selectedUserId == data.sender_id || selectedUserId == data.receiver_id) {
                renderNewMessage(data);
            }
        });
    }


    function renderNewMessage(data) {
        let chatMessages = document.getElementById("chatMessages");

        let messageClass = data.sender_id == {{ auth()->id() }} ? 'chat-message-right' : 'chat-message-left';

        chatMessages.innerHTML += `
            <div class="${messageClass} pb-4">
                <div>

                    <div class="text-muted small text-nowrap mt-2">${data.created_at}</div>
                </div>
                <div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3">
                    ${data.message}
                </div>
            </div>
        `;

        chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll al último mensaje
    }
</script>





<script>
    let selectedConversation = null;

    window.Echo.channel(`chat.${selectedConversation}`)
        .listen('.MessageSent', (data) => {
            console.log("Nuevo mensaje recibido:", data);
            loadChat(selectedUserId, document.getElementById("chatTitle").innerText);
        });
</script>

<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">

</script>
</body>
</html>
