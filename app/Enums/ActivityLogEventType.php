<?php

namespace App\Enums;

enum ActivityLogEventType: string
{
    case Created = 'created';
    case StatusChange = 'status_change';
    case ParticipantAdded = 'participant_added';
    case ParticipantRemoved = 'participant_removed';

    public function label(): string
    {
        return match ($this) {
            self::Created => __('activities.timeline.created'),
            self::StatusChange => __('activities.timeline.status_change'),
            self::ParticipantAdded => __('activities.timeline.participant_added'),
            self::ParticipantRemoved => __('activities.timeline.participant_removed'),
        };
    }
}
