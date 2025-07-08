<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use OpenAI;

class TicketClassifier
{
    public function classify(Ticket $ticket): array
    {
        $key = 'openai-api-global-limit';
        if (!RateLimiter::attempt($key, 10)) {
            return [
                'category' => 'question',
                'explanation' => 'Rate limit exceeded. Try again later.',
                'confidence' => 0.0,
            ];
        }

        if (!config('openai.api_key') || !config('services.openai.classify_enabled')) {
            return $this->mockResult();
        }

        $openai = OpenAI::client(config('openai.api_key'));

        $prompt = <<<PROMPT
            You are an AI assistant. Given the ticket subject and body, classify the category.

            Return JSON only with the following keys:
            - category: one of [bug, feature, question]
            - explanation: brief reason for your decision
            - confidence: a float from 0 to 1

            Subject: {$ticket->subject}
            Body: {$ticket->body}
            PROMPT;

        $response = $openai->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a ticket classification assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $json = json_decode($response->choices[0]->message->content ?? '', true);

        return [
            'category' => $json['category'] ?? 'question',
            'explanation' => $json['explanation'] ?? 'No explanation provided.',
            'confidence' => $json['confidence'] ?? 0.5,
        ];
    }

    private function mockResult(): array
    {
        $categories = ['bug', 'feature', 'question'];

        return [
            'category' => $categories[array_rand($categories)],
            'explanation' => 'Mock result - OpenAI disabled',
            'confidence' => round(mt_rand() / mt_getrandmax(), 2),
        ];
    }
}
