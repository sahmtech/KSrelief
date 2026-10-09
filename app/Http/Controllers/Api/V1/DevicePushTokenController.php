<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Push\RegisterDevicePushTokenRequest;
use App\Services\Push\DevicePushTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DevicePushTokenController extends Controller
{
    public function __construct(
        private readonly DevicePushTokenService $tokenService,
    ) {}

    public function store(RegisterDevicePushTokenRequest $request): JsonResponse
    {
        $token = $this->tokenService->register(
            $request->user(),
            $request->validated('fcm_token'),
            $request->validated('platform'),
            $request->validated('device_name'),
        );

        return response()->json([
            'message' => __('push.messages.token_registered'),
            'data' => [
                'id' => $token->id,
                'platform' => $token->platform,
                'device_name' => $token->device_name,
                'updated_at' => $token->updated_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['nullable', 'string', 'max:512'],
        ]);

        $deleted = $this->tokenService->unregister(
            $request->user(),
            $validated['fcm_token'] ?? null,
        );

        return response()->json([
            'message' => __('push.messages.token_removed'),
            'deleted' => $deleted,
        ]);
    }
}
