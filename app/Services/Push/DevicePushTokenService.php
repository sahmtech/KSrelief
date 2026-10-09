<?php

namespace App\Services\Push;

use App\Models\DevicePushToken;
use App\Models\User;

final class DevicePushTokenService
{
    public function register(User $user, string $fcmToken, string $platform, ?string $deviceName = null): DevicePushToken
    {
        $token = DevicePushToken::query()->updateOrCreate(
            ['fcm_token' => $fcmToken],
            [
                'user_id' => $user->id,
                'platform' => $platform,
                'device_name' => $deviceName,
                'last_used_at' => now(),
            ]
        );

        return $token;
    }

    public function unregister(User $user, ?string $fcmToken = null): int
    {
        $query = DevicePushToken::query()->where('user_id', $user->id);

        if ($fcmToken !== null && $fcmToken !== '') {
            $query->where('fcm_token', $fcmToken);
        }

        return $query->delete();
    }
}
