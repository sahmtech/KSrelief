<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Push\BroadcastPushNotificationRequest;
use App\Models\PushNotificationDispatch;
use App\Models\User;
use App\Services\PatientAccessService;
use App\Services\Push\PushDispatchService;
use App\Services\Push\PushRecipientResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct(
        private readonly PushDispatchService $dispatchService,
        private readonly PushRecipientResolver $recipients,
        private readonly PatientAccessService $campaignAccess,
    ) {}

    public function recipients(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('push.broadcast'), 403);

        $campaignId = $request->integer('campaign_id') ?: null;
        $roles = config('push_notifications.recipient_roles', ['doctor']);

        $query = User::query()
            ->where('status', UserStatus::Active)
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', $roles))
            ->with('roles:id,name')
            ->withCount('devicePushTokens')
            ->orderBy('name');

        if ($campaignId) {
            $query->where(function (Builder $q) use ($campaignId): void {
                $q->whereHas('campaignAssignments', fn (Builder $cq) => $cq->where('campaign_id', $campaignId))
                    ->orWhereHas('member.campaignAssignments', fn (Builder $mq) => $mq->where('campaign_id', $campaignId));
            });
        }

        $allowed = $this->campaignAccess->allowedCampaignIds($request->user());

        if ($allowed !== null) {
            $query->where(function (Builder $q) use ($allowed): void {
                $q->whereHas('campaignAssignments', fn (Builder $cq) => $cq->whereIn('campaign_id', $allowed))
                    ->orWhereHas('member.campaignAssignments', fn (Builder $mq) => $mq->whereIn('campaign_id', $allowed));
            });
        }

        $users = $query->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'),
                'device_tokens_count' => $user->device_push_tokens_count,
            ]),
        ]);
    }

    public function broadcast(BroadcastPushNotificationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $target = $validated['target'];
        $extraData = $validated['data'] ?? [];

        $dispatch = match ($target) {
            'all_doctors' => $this->dispatchService->adminBroadcast(
                $request->user(),
                $validated['title'],
                $validated['body'],
                $extraData,
                userIds: null,
                campaignId: null,
                allClinical: true,
            ),
            'campaign_doctors' => $this->dispatchService->adminBroadcast(
                $request->user(),
                $validated['title'],
                $validated['body'],
                array_merge($extraData, ['campaign_id' => $validated['campaign_id']]),
                userIds: null,
                campaignId: (int) $validated['campaign_id'],
                allClinical: true,
            ),
            'user_ids' => $this->dispatchService->adminBroadcast(
                $request->user(),
                $validated['title'],
                $validated['body'],
                $extraData,
                userIds: array_map('intval', $validated['user_ids']),
                campaignId: null,
                allClinical: false,
            ),
        };

        return response()->json([
            'message' => __('push.messages.broadcast_queued'),
            'dispatch' => $this->dispatchPayload($dispatch),
        ], 202);
    }

    public function history(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('push.broadcast'), 403);

        $perPage = min(max((int) $request->query('per_page', 20), 1), 50);

        $items = PushNotificationDispatch::query()
            ->with('sender:id,name')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'data' => $items->through(fn (PushNotificationDispatch $row) => $this->dispatchPayload($row)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dispatchPayload(PushNotificationDispatch $dispatch): array
    {
        return [
            'id' => $dispatch->id,
            'type' => $dispatch->type,
            'title' => $dispatch->title,
            'body' => $dispatch->body,
            'data' => $dispatch->data,
            'target_users' => $dispatch->target_users,
            'tokens_attempted' => $dispatch->tokens_attempted,
            'tokens_succeeded' => $dispatch->tokens_succeeded,
            'tokens_failed' => $dispatch->tokens_failed,
            'sent_by' => $dispatch->sender ? [
                'id' => $dispatch->sender->id,
                'name' => $dispatch->sender->name,
            ] : null,
            'created_at' => $dispatch->created_at?->toIso8601String(),
        ];
    }
}
