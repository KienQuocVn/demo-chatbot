<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function chat(Request $req)
    {
        $apiKey = env('GROQ_API_KEY', '');
        $model = $req->string('model')->toString() ?: 'llama-3.3-70b-versatile';
        // [{role:'user' | 'system' | 'assistant', content: '...'}];
        $messages = $req->input('messages', []);

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => (float)($req->input('temperature', 0.5)),
            'stream' => false,
        ];

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey,
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 60,
        ]);

        $out = curl_exec($ch);

        curl_close($ch);
        $json = json_decode($out ?: '{}', true);

        $text = $json['choices'][0]['message']['content'] ?? '[no content]';

        return response()->json(['content' => $text, 'raw' => $json,]);
    }
}