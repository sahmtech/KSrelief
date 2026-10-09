<?php

namespace App\Services\Push;

use App\Enums\PushNotificationType;
use App\Jobs\DeliverPushNotificationJob;
use App\Models\DevicePushToken;
use App\Models\PushNotificationDispatch;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

final class PushDispatchService
{
    public function __construct(
        private readonly FcmV1Client $fcm,
        private readonly PushRecipientResolver $recipients,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyCampaign(
        int $campaignId,
        PushNotificationType $type,
        string $title,
        string $body,
        array $data = [],
        ?User $actor = null,
    ): void {
        if (! $this->shouldSendEvent($type)) {
            return;
        }

        $users = $this->recipients->forCampaign($campaignId, $actor?->id);

        $this->deliverToUsers($users, $type, $title, $body, $data, $actor);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>|null  $userIds
     */
    public function adminBroadcast(
        User $sender,
        string $title,
        string $body,
        array $data = [],
        ?array $userIds = null,
        ?int $campaignId = null,
        bool $allClinical = false,
    ): PushNotificationDispatch {
        abort_unless($this->recipients->canBroadcast($sender), 403);

        if ($userIds !== null) {
            $userIds = $this->recipients->filterUserIdsForSender($sender, $userIds, $campaignId);
            $users = $this->recipients->forUserIds($userIds);
        } elseif ($allClinical) {
            $users = $this->recipients->allClinicalUsers($campaignId);
            if ($campaignId === null && $this->campaignAccessLimited($sender)) {
                $allowed = app(\App\Services\PatientAccessService::class)->allowedCampaignIds($sender) ?? [];
                $users = $users->filter(function (User $user) use ($allowed): bool {
                    return $user->campaignAssignments()->whereIn('campaign_id', $allowed)->exists()
                        || ($user->member && $user->member->campaignAssignments()->whereIn('campaign_id', $allowed)->exists());
                });
            }
        } else {
            abort(422, __('push.errors.no_recipients_selected'));
        }

        $data['type'] = PushNotificationType::AdminBroadcast->value;

        return $this->deliverToUsers(
            $users,
            PushNotificationType::AdminBroadcast,
            $title,
            $body,
            $data,
            $sender,
            force: true,
        );
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  array<string, mixed>  $data
     */
    private function deliverToUsers(
        Collection $users,
        PushNotificationType $type,
        string $title,
        string $body,
        array $data,
        ?User $sender = null,
        bool $force = false,
    ): PushNotificationDispatch {
        $data = array_merge(['type' => $type->value], $data);

        $dispatch = PushNotificationDispatch::create([
            'sent_by' => $sender?->id,
            'type' => $type->value,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'target_users' => $users->count(),
        ]);

        if (! config('push_notifications.enabled', true) || (! $force && ! $this->fcm->isConfigured())) {
            Log::info('Push skipped (disabled or FCM not configured)', ['type' => $type->value]);

            return $dispatch;
        }

        $tokenIds = $users
            ->flatMap(fn (User $user) => $user->devicePushTokens->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $dispatch->update(['tokens_attempted' => count($tokenIds)]);

        if ($tokenIds === []) {
            return $dispatch;
        }

        if (config('push_notifications.queue', true)) {
            DeliverPushNotificationJob::dispatch($dispatch->id, $tokenIds, $title, $body, $data);

            return $dispatch;
        }

        $this->sendToTokenIds($dispatch->id, $tokenIds, $title, $body, $data);

        return $dispatch->fresh() ?? $dispatch;
    }

    /**
     * @param  list<int>  $tokenIds
     * @param  array<string, mixed>  $data
     */
    public function sendToTokenIds(int $dispatchId, array $tokenIds, string $title, string $body, array $data): void
    {
        $tokens = DevicePushToken::query()->whereIn('id', $tokenIds)->get();
        $success = 0;
        $failed = 0;

        foreach ($tokens as $tokenModel) {
            $result = $this->fcm->sendToDevice($tokenModel->fcm_token, $title, $body, $data);

            if ($result['success']) {
                $success++;
                $tokenModel->forceFill(['last_used_at' => now()])->save();
            } else {
                $failed++;
                if ($result['should_drop_token']) {
                    $tokenModel->delete();
                }
            }
        }

        PushNotificationDispatch::query()->whereKey($dispatchId)->update([
            'tokens_succeeded' => $success,
            'tokens_failed' => $failed,
        ]);
    }

    private function shouldSendEvent(PushNotificationType $type): bool
    {
        if (! config('push_notifications.enabled', true)) {
            return false;
        }

        if ($type === PushNotificationType::AdminBroadcast) {
            return true;
        }

        return (bool) config('push_notifications.events.'.$type->configKey(), true);
    }

    private function campaignAccessLimited(User $sender): bool
    {
        return app(\App\Services\PatientAccessService::class)->allowedCampaignIds($sender) !== null;
    }
}
