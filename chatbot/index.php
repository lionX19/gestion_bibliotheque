<?php
require_once 'chatbot.php';
$chatbot = new Chatbot();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $response = $chatbot->processMessage($_POST['message']);
    echo json_encode(['response' => $response]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Bibliothèque</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .chat-container {
            width: 100%;
            max-width: 600px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .chat-header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            text-align: center;
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            margin-bottom: 10px;
        }

        .user-message {
            background-color: #e3f2fd;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        .bot-message {
            background-color: #f1f1f1;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .chat-input {
            display: flex;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        #message-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            margin-right: 10px;
            font-size: 16px;
        }

        #send-button {
            background-color: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        #send-button:hover {
            background-color: #34495e;
        }

        @media (max-width: 480px) {
            .chat-container {
                height: 100vh;
                border-radius: 0;
            }

            .chat-messages {
                height: calc(100vh - 120px);
            }
        }
    </style>
</head>

<body>
    <div class="chat-container">
        <div class="chat-header">
            <h2>Assistant Bibliothèque</h2>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="message bot-message">
                Bonjour ! Je suis votre assistant bibliothèque. Comment puis-je vous aider aujourd'hui ?
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="message-input" placeholder="Tapez votre message ici..." autocomplete="off">
            <button id="send-button">Envoyer</button>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chat-messages');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');

        function addMessage(message, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
            messageDiv.textContent = message;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (message) {
                addMessage(message, true);
                messageInput.value = '';

                try {
                    const response = await fetch('index.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `message=${encodeURIComponent(message)}`
                    });

                    const data = await response.json();
                    addMessage(data.response);
                } catch (error) {
                    console.error('Error:', error);
                    addMessage('Désolé, une erreur est survenue. Veuillez réessayer.');
                }
            }
        }

        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>

</html>