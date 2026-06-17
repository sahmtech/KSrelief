<?php

namespace App\Services;

use App\Enums\PassengerType;
use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Models\CampaignMember;
use App\Models\Patient;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use App\Models\TransportationTripStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransportationService
{
    public const AUDIT_TRIP_CREATED = 'transportation.trip.created';

    public const AUDIT_TRIP_UPDATED = 'transportation.trip.updated';

    public const AUDIT_TRIP_DELETED = 'transportation.trip.deleted';

    public const AUDIT_PASSENGER_ADDED = 'transportation.passenger.added';

    public const AUDIT_PASSENGER_REMOVED = 'transportation.passenger.removed';

    public const AUDIT_STATUS_CHANGED = 'transportation.status.changed';

    public function createTrip(array $data, User $user): TransportationTrip
    {
        return DB::transaction(function () use ($data, $user): TransportationTrip {
            $trip = TransportationTrip::create([
                'campaign_id' => $data['campaign_id'],
                'trip_code' => $this->generateTripCode(),
                'trip_date' => $data['trip_date'],
                'departure_time' => $data['departure_time'],
                'arrival_time' => $data['arrival_time'] ?? null,
                'from_location_id' => $data['from_location_id'],
                'to_location_id' => $data['to_location_id'],
                'trip_type' => $data['trip_type'],
                'vehicle_number' => $data['vehicle_number'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => TripStatus::Planned,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->logStatusChange($trip, null, TripStatus::Planned, $user, __('transportation.messages.trip_created'));

            // Future: dispatch audit event self::AUDIT_TRIP_CREATED

            return $trip->load([
                'campaign',
                'fromLocation',
                'toLocation',
                'creator',
                'passengers.member',
                'passengers.patient',
            ]);
        });
    }

    public function updateTrip(TransportationTrip $trip, array $data, User $user): TransportationTrip
    {
        if (! $trip->isEditable()) {
            throw new InvalidArgumentException(__('transportation.errors.trip_not_editable'));
        }

        return DB::transaction(function () use ($trip, $data, $user): TransportationTrip {
            $trip->update([
                'campaign_id' => $data['campaign_id'] ?? $trip->campaign_id,
                'trip_date' => $data['trip_date'] ?? $trip->trip_date,
                'departure_time' => $data['departure_time'] ?? $trip->departure_time,
                'arrival_time' => array_key_exists('arrival_time', $data) ? $data['arrival_time'] : $trip->arrival_time,
                'from_location_id' => $data['from_location_id'] ?? $trip->from_location_id,
                'to_location_id' => $data['to_location_id'] ?? $trip->to_location_id,
                'trip_type' => $data['trip_type'] ?? $trip->trip_type,
                'vehicle_number' => array_key_exists('vehicle_number', $data) ? $data['vehicle_number'] : $trip->vehicle_number,
                'driver_name' => array_key_exists('driver_name', $data) ? $data['driver_name'] : $trip->driver_name,
                'capacity' => array_key_exists('capacity', $data) ? $data['capacity'] : $trip->capacity,
                'notes' => array_key_exists('notes', $data) ? $data['notes'] : $trip->notes,
                'updated_by' => $user->id,
            ]);

            // Future: dispatch audit event self::AUDIT_TRIP_UPDATED

            return $trip->fresh([
                'campaign',
                'fromLocation',
                'toLocation',
                'creator',
                'updater',
                'passengers.member',
                'passengers.patient',
            ]);
        });
    }

    public function deleteTrip(TransportationTrip $trip): void
    {
        if ($trip->status === TripStatus::InProgress) {
            throw new InvalidArgumentException(__('transportation.errors.cannot_delete_in_progress'));
        }

        DB::transaction(function () use ($trip): void {
            // Future: dispatch audit event self::AUDIT_TRIP_DELETED
            $trip->delete();
        });
    }

    public function addPassenger(TransportationTrip $trip, array $data, User $user): TransportationTripPassenger
    {
        if (! $trip->isEditable()) {
            throw new InvalidArgumentException(__('transportation.errors.trip_not_editable'));
        }

        $passengerType = PassengerType::from($data['passenger_type']);

        return DB::transaction(function () use ($trip, $data, $passengerType, $user): TransportationTripPassenger {
            if ($passengerType === PassengerType::Member) {
                $memberId = (int) $data['member_id'];
                $this->assertMemberAssignedToCampaign($trip->campaign_id, $memberId);
                $this->assertPassengerNotOnTrip($trip, PassengerType::Member, $memberId);

                if ($trip->trip_type === TripType::PatientTransport) {
                    throw new InvalidArgumentException(__('transportation.errors.invalid_passenger_for_trip_type'));
                }
            } else {
                $patientId = (int) $data['patient_id'];
                $this->assertPatientBelongsToCampaign($trip->campaign_id, $patientId);
                $this->assertPassengerNotOnTrip($trip, PassengerType::Patient, $patientId);

                if ($trip->trip_type === TripType::MemberTransport) {
                    throw new InvalidArgumentException(__('transportation.errors.invalid_passenger_for_trip_type'));
                }
            }

            if ($trip->capacity && $trip->passengers()->count() >= $trip->capacity) {
                throw new InvalidArgumentException(__('transportation.errors.capacity_reached'));
            }

            $passenger = TransportationTripPassenger::create([
                'trip_id' => $trip->id,
                'passenger_type' => $passengerType,
                'member_id' => $passengerType === PassengerType::Member ? $data['member_id'] : null,
                'patient_id' => $passengerType === PassengerType::Patient ? $data['patient_id'] : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncTripTypeFromPassengers($trip);

            // Future: dispatch audit event self::AUDIT_PASSENGER_ADDED

            return $passenger->load(['member.memberRole', 'patient']);
        });
    }

    public function removePassenger(TransportationTripPassenger $passenger): void
    {
        $trip = $passenger->trip;

        if (! $trip->isEditable()) {
            throw new InvalidArgumentException(__('transportation.errors.trip_not_editable'));
        }

        DB::transaction(function () use ($passenger, $trip): void {
            // Future: dispatch audit event self::AUDIT_PASSENGER_REMOVED
            $passenger->delete();
            $this->syncTripTypeFromPassengers($trip);
        });
    }

    public function changeStatus(
        TransportationTrip $trip,
        TripStatus $newStatus,
        User $user,
        ?string $notes = null
    ): TransportationTrip {
        $current = $trip->status;

        if (! $current->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(__('transportation.errors.invalid_status_transition'));
        }

        return DB::transaction(function () use ($trip, $current, $newStatus, $user, $notes): TransportationTrip {
            $trip->update([
                'status' => $newStatus,
                'updated_by' => $user->id,
            ]);

            $this->logStatusChange($trip, $current, $newStatus, $user, $notes);

            // Future: dispatch audit event self::AUDIT_STATUS_CHANGED

            return $trip->fresh([
                'campaign',
                'fromLocation',
                'toLocation',
                'statusLogs.changedBy',
            ]);
        });
    }

    public function startTrip(TransportationTrip $trip, User $user, ?string $notes = null): TransportationTrip
    {
        return $this->changeStatus($trip, TripStatus::InProgress, $user, $notes);
    }

    public function completeTrip(TransportationTrip $trip, User $user, ?string $notes = null): TransportationTrip
    {
        return $this->changeStatus($trip, TripStatus::Completed, $user, $notes);
    }

    public function cancelTrip(TransportationTrip $trip, User $user, ?string $notes = null): TransportationTrip
    {
        return $this->changeStatus($trip, TripStatus::Cancelled, $user, $notes);
    }

    public function assertMemberAssignedToCampaign(int $campaignId, int $memberId): void
    {
        $assigned = CampaignMember::query()
            ->where('campaign_id', $campaignId)
            ->where('member_id', $memberId)
            ->where(function ($query): void {
                $query->whereNull('assigned_to')
                    ->orWhereDate('assigned_to', '>=', now());
            })
            ->exists();

        if (! $assigned) {
            throw new InvalidArgumentException(__('transportation.errors.member_not_assigned'));
        }
    }

    public function assertPatientBelongsToCampaign(int $campaignId, int $patientId): void
    {
        $belongs = Patient::query()
            ->where('id', $patientId)
            ->where('campaign_id', $campaignId)
            ->exists();

        if (! $belongs) {
            throw new InvalidArgumentException(__('transportation.errors.patient_not_in_campaign'));
        }
    }

    private function assertPassengerNotOnTrip(TransportationTrip $trip, PassengerType $type, int $id): void
    {
        $exists = TransportationTripPassenger::query()
            ->where('trip_id', $trip->id)
            ->when($type === PassengerType::Member, fn ($q) => $q->where('member_id', $id))
            ->when($type === PassengerType::Patient, fn ($q) => $q->where('patient_id', $id))
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException(__('transportation.errors.passenger_already_on_trip'));
        }
    }

    private function syncTripTypeFromPassengers(TransportationTrip $trip): void
    {
        $trip->load('passengers');

        if ($trip->passengers->isEmpty()) {
            return;
        }

        $hasMembers = $trip->passengers->contains(fn ($p) => $p->passenger_type === PassengerType::Member);
        $hasPatients = $trip->passengers->contains(fn ($p) => $p->passenger_type === PassengerType::Patient);

        $type = match (true) {
            $hasMembers && $hasPatients => TripType::MixedTransport,
            $hasMembers => TripType::MemberTransport,
            default => TripType::PatientTransport,
        };

        if ($trip->trip_type !== $type) {
            $trip->update(['trip_type' => $type]);
        }
    }

    private function logStatusChange(
        TransportationTrip $trip,
        ?TripStatus $oldStatus,
        TripStatus $newStatus,
        User $user,
        ?string $notes = null
    ): void {
        TransportationTripStatusLog::create([
            'trip_id' => $trip->id,
            'old_status' => $oldStatus?->value,
            'new_status' => $newStatus->value,
            'changed_by' => $user->id,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    private function generateTripCode(): string
    {
        $year = now()->format('Y');
        $prefix = "TRP-{$year}-";

        $lastCode = TransportationTrip::withTrashed()
            ->where('trip_code', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('trip_code');

        $sequence = 1;

        if ($lastCode && preg_match('/-(\d+)$/', $lastCode, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
