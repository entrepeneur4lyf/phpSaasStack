<?php
declare(strict_types=1);

namespace Src\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PiAPIClient
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.piapi.ai/v1/',
            'headers' => [
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);
        $this->apiKey = $apiKey;
    }

    public function chatCompletion(string $prompt): string
    {
        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4o-mini', // Using the gpt-4o-mini model as an example
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['choices'][0]['message']['content'] ?? 'No response from AI';
        } catch (GuzzleException $e) {
            // Log the error and return a user-friendly message
            error_log("PiAPI Error: " . $e->getMessage());
            return "An error occurred while processing your request.";
        }
    }

    public static function create(string $apiKey): self
    {
        return new self($apiKey);
    }
}