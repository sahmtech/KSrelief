<?php

namespace App\Services\Push;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

final class FcmV1Client
{
    private const MESSAGING_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private ?string $projectId = null;

    /** @var array<string, mixed>|null */
    private ?array $credentials = null;

    public function isConfigured(): bool
    {
        $path = config('push_notifications.credentials_path');

        return is_string($path) && $path !== '' && is_readable($path);
    }

    /**
     * @return array{success: bool, error: string|null, should_drop_token: bool}
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'FCM credentials missing', 'should_drop_token' => false];
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $this->stringifyData($data),
                'android' => ['priority' => 'HIGH'],
                'apns' => [
                    'headers' => ['apns-priority' => '10'],
                    'payload' => ['aps' => ['sound' => 'default']],
                ],
            ],
        ];

        try {
            /** @var Response $response */
            $response = Http::timeout(15)
                ->withToken($this->accessToken())
                ->acceptJson()
                ->post($this->messagesEndpoint(), $payload);
        } catch (\Throwable $e) {
            Log::warning('FCM HTTP error', ['message' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage(), 'should_drop_token' => false];
        }

        if ($response->successful()) {
            return ['success' => true, 'error' => null, 'should_drop_token' => false];
        }

        $error = $response->json('error.message') ?? $response->body();
        $status = $response->json('error.status') ?? '';
        $shouldDrop = in_array($status, [
            'NOT_FOUND',
            'UNREGISTERED',
            'INVALID_ARGUMENT',
        ], true) || str_contains(strtolower((string) $error), 'not found');

        Log::info('FCM send failed', [
            'status' => $response->status(),
            'error' => $error,
            'token_prefix' => substr($fcmToken, 0, 12),
        ]);

        return ['success' => false, 'error' => (string) $error, 'should_drop_token' => $shouldDrop];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $out[(string) $key] = (string) ($value ?? '');

                continue;
            }

            $out[(string) $key] = json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
        }

        return $out;
    }

    private function messagesEndpoint(): string
    {
        return 'https://fcm.googleapis.com/v1/projects/'.$this->projectId().'/messages:send';
    }

    private function accessToken(): string
    {
        $credentials = new ServiceAccountCredentials(
            self::MESSAGING_SCOPE,
            $this->credentialsArray()
        );

        $token = $credentials->fetchAuthToken();

        if (! isset($token['access_token'])) {
            throw new RuntimeException('Unable to obtain Firebase access token.');
        }

        return $token['access_token'];
    }

    private function projectId(): string
    {
        if ($this->projectId !== null) {
            return $this->projectId;
        }

        $projectId = $this->credentialsArray()['project_id'] ?? null;

        if (! is_string($projectId) || $projectId === '') {
            throw new InvalidArgumentException('Firebase credentials JSON must contain project_id.');
        }

        return $this->projectId = $projectId;
    }

    /**
     * @return array<string, mixed>
     */
    private function credentialsArray(): array
    {
        if ($this->credentials !== null) {
            return $this->credentials;
        }

        $path = config('push_notifications.credentials_path');

        if (! is_readable($path)) {
            throw new InvalidArgumentException('Firebase credentials file is not readable.');
        }

        $json = file_get_contents($path);

        if ($json === false) {
            throw new InvalidArgumentException('Unable to read Firebase credentials file.');
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Firebase credentials JSON is invalid.');
        }

        return $this->credentials = $decoded;
    }
}
