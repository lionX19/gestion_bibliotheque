<?php

class ChatGPT
{
    private $apiKey;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-3.5-turbo';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getResponse($message, $context = '')
    {
        try {
            $messages = [];

            // Ajouter le contexte si fourni
            if (!empty($context)) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $context
                ];
            }

            // Ajouter le message de l'utilisateur
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];

            $data = [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 500
            ];

            $ch = curl_init($this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]);

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new Exception("Erreur cURL: " . $error);
            }

            $result = json_decode($response, true);

            if (isset($result['error'])) {
                throw new Exception("Erreur API: " . $result['error']['message']);
            }

            return $result['choices'][0]['message']['content'];
        } catch (Exception $e) {
            error_log("Erreur ChatGPT: " . $e->getMessage());
            return "Désolé, je ne peux pas répondre pour le moment. Veuillez réessayer plus tard.";
        }
    }
}
