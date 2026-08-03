<?php

namespace App\Services\AiReport;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAiClient
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?? env('OPENAI_API_KEY') ?? '';
        if (empty($this->apiKey)) {
            $this->logError('Falta la clave API de OpenAI en la configuración.');
        }
    }

    /**
     * Hace la llamada a la API de OpenAI.
     * 
     * @param string $model
     * @param array $messages
     * @param float $temperature
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function chat(string $model, array $messages, float $temperature = 0.0, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new Exception('Falta la clave API de OpenAI en la configuración.');
        }

        $payload = array_merge([
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
        ], $options);

        $timeout = $options['timeout'] ?? 120;
        unset($payload['timeout']); // Remueve campos no válidos para la API

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout($timeout)
            ->post($this->apiUrl, $payload);

            if ($response->failed()) {
                $this->logError('OpenAI API request failed.', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'payload' => $payload
                ]);
                throw new Exception('OpenAI API returned an error: ' . $response->body(), $response->status());
            }

            $data = $response->json();

            // Validación del finish_reason
            $finishReason = $data['choices'][0]['finish_reason'] ?? null;
            if ($finishReason === 'length') {
                $this->logWarning('OpenAI API response truncated due to token limit.', [
                    'usage' => $data['usage'] ?? 'unknown'
                ]);
            }

            return $data;

        } catch (Exception $e) {
            $this->logError('Excepción al llamar a OpenAI API.', [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function logError(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/openai.log'),
        ])->error($message, $context);
    }

    protected function logWarning(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/openai.log'),
        ])->warning($message, $context);
    }
}
