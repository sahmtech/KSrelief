<?php

namespace Database\Seeders;

use App\Enums\PassengerType;
use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Models\Campaign;
use App\Models\Member;
use App\Models\Patient;
use App\Models\TransportationLocation;
use App\Models\TransportationTrip;
use App\Models\TransportationTripPassenger;
use App\Models\TransportationTripStatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransportationTripsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();
        $campaign = Campaign::query()->first();
        $locations = TransportationLocation::query()->active()->get();

        if (! $admin || ! $campaign || $locations->count() < 2) {
            return;
        }

        $hotel = $locations->firstWhere('type', 'hotel') ?? $locations->first();
        $hospital = $locations->firstWhere('type', 'hospital') ?? $locations->skip(1)->first();
        $airport = $locations->firstWhere('type', 'airport') ?? $locations->last();

        $member = Member::query()->whereHas('campaignAssignments', fn ($q) => $q->where('campaign_id', $campaign->id))->first();
        $patient = Patient::query()->where('campaign_id', $campaign->id)->first();

        $trips = [
            [
                'code' => 'TRP-'.now()->format('Y').'-0001',
                'date' => now()->toDateString(),
                'departure' => '08:00',
                'arrival' => '08:45',
                'from' => $hotel->id,
                'to' => $hospital->id,
                'type' => TripType::MixedTransport,
                'status' => TripStatus::Planned,
                'vehicle' => 'BUS-101',
                'driver' => 'Mohammed Ali',
                'capacity' => 20,
            ],
            [
                'code' => 'TRP-'.now()->format('Y').'-0002',
                'date' => now()->addDay()->toDateString(),
                'departure' => '14:00',
                'arrival' => '14:30',
                'from' => $airport->id,
                'to' => $hotel->id,
                'type' => TripType::MemberTransport,
                'status' => TripStatus::Planned,
                'vehicle' => 'VAN-205',
                'driver' => 'Saeed Hassan',
                'capacity' => 8,
            ],
            [
                'code' => 'TRP-'.now()->format('Y').'-0003',
                'date' => now()->subDay()->toDateString(),
                'departure' => '07:30',
                'arrival' => '08:00',
                'from' => $hotel->id,
                'to' => $hospital->id,
                'type' => TripType::PatientTransport,
                'status' => TripStatus::Completed,
                'vehicle' => 'BUS-102',
                'driver' => 'Khalid Omar',
                'capacity' => 15,
            ],
        ];

        foreach ($trips as $data) {
            $trip = TransportationTrip::query()->updateOrCreate(
                ['trip_code' => $data['code']],
                [
                    'campaign_id' => $campaign->id,
                    'trip_date' => $data['date'],
                    'departure_time' => $data['departure'],
                    'arrival_time' => $data['arrival'],
                    'from_location_id' => $data['from'],
                    'to_location_id' => $data['to'],
                    'trip_type' => $data['type'],
                    'vehicle_number' => $data['vehicle'],
                    'driver_name' => $data['driver'],
                    'capacity' => $data['capacity'],
                    'notes' => 'Seeded transportation trip.',
                    'status' => $data['status'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );

            if (! $trip->statusLogs()->exists()) {
                TransportationTripStatusLog::query()->create([
                    'trip_id' => $trip->id,
                    'old_status' => null,
                    'new_status' => TripStatus::Planned->value,
                    'changed_by' => $admin->id,
                    'notes' => __('transportation.messages.trip_created'),
                    'created_at' => now(),
                ]);

                if ($data['status'] === TripStatus::Completed) {
                    TransportationTripStatusLog::query()->create([
                        'trip_id' => $trip->id,
                        'old_status' => TripStatus::Planned->value,
                        'new_status' => TripStatus::InProgress->value,
                        'changed_by' => $admin->id,
                        'notes' => null,
                        'created_at' => now()->subHours(2),
                    ]);
                    TransportationTripStatusLog::query()->create([
                        'trip_id' => $trip->id,
                        'old_status' => TripStatus::InProgress->value,
                        'new_status' => TripStatus::Completed->value,
                        'changed_by' => $admin->id,
                        'notes' => null,
                        'created_at' => now()->subHour(),
                    ]);
                }
            }

            if ($member && ! $trip->passengers()->where('member_id', $member->id)->exists()) {
                TransportationTripPassenger::query()->create([
                    'trip_id' => $trip->id,
                    'passenger_type' => PassengerType::Member,
                    'member_id' => $member->id,
                    'notes' => 'Campaign team member',
                ]);
            }

            if ($patient && $data['type'] !== TripType::MemberTransport && ! $trip->passengers()->where('patient_id', $patient->id)->exists()) {
                TransportationTripPassenger::query()->create([
                    'trip_id' => $trip->id,
                    'passenger_type' => PassengerType::Patient,
                    'patient_id' => $patient->id,
                    'notes' => 'Patient transport',
                ]);
            }
        }
    }
}
