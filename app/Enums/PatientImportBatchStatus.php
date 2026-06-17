<?php

namespace App\Enums;

enum PatientImportBatchStatus: string
{
    case Uploaded = 'uploaded';
    case Processing = 'processing';
    case Review = 'review';
    case Approved = 'approved';
    case Completed = 'completed';
    case Failed = 'failed';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Uploaded => __('patients.import.status.uploaded'),
            self::Processing => __('patients.import.status.processing'),
            self::Review => __('patients.import.status.review'),
            self::Approved => __('patients.import.status.approved'),
            self::Completed => __('patients.import.status.completed'),
            self::Failed => __('patients.import.status.failed'),
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Uploaded => 'badge-status--secondary',
            self::Processing => 'badge-status--warning',
            self::Review => 'badge-status--primary',
            self::Approved => 'badge-status--primary',
            self::Completed => 'badge-status--active',
            self::Failed => 'badge-status--danger',
        };
    }

    public function isReviewable(): bool
    {
        return $this === self::Review;
    }

    public function isApprovable(): bool
    {
        return $this === self::Review;
    }
}
