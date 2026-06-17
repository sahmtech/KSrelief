<?php

namespace App\Support;

final class MemberImportResult
{
    /** @param list<array{row: int, message: string}> $errors */
    public function __construct(
        public int $success = 0,
        public int $failed = 0,
        public int $skipped = 0,
        public array $errors = [],
    ) {}

    public function hasErrors(): bool
    {
        return $this->failed > 0 || $this->skipped > 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'failed' => $this->failed,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            success: (int) ($data['success'] ?? 0),
            failed: (int) ($data['failed'] ?? 0),
            skipped: (int) ($data['skipped'] ?? 0),
            errors: $data['errors'] ?? [],
        );
    }
}
