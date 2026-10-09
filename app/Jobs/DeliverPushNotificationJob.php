<?php

namespace App\Jobs;

use App\Services\Push\PushDispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<int>  $tokenIds
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $dispatchId,
        public array $tokenIds,
        public string $title,
        public string $body,
        public array $data,
    ) {}

    public function handle(PushDispatchService $dispatchService): void
    {
        $dispatchService->sendToTokenIds(
            $this->dispatchId,
            $this->tokenIds,
            $this->title,
            $this->body,
            $this->data,
        );
    }
}
