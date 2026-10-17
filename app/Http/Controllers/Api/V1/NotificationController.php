<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 20), 1), 50);

        $items = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $items->through(fn (DatabaseNotification $row) => $this->payload($row)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(DatabaseNotification $notification): array
    {
        /** @var array<string, mixed> $data */
        $data = $notification->data;

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? '',
            'body' => $data['body'] ?? '',
            'type' => $data['type'] ?? null,
            'data' => $data['data'] ?? [],
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
