<?php

namespace Database\Seeders;

use App\Enums\MemberStatus;
use App\Models\Member;
use App\Models\MemberRole;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class MembersSeeder extends Seeder
{
    public function run(): void
    {
        $roles = MemberRole::query()->pluck('id', 'code');
        $specialties = Specialty::query()->active()->limit(5)->pluck('id');

        $samples = [
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Harbi',
                'mobile' => '+966501000001',
                'email' => 'ahmed.harbi@example.com',
                'gender' => 'male',
                'nationality' => 'Saudi Arabia',
                'member_role_id' => $roles['doctor'] ?? $roles->first(),
                'specialty_id' => $specialties->first(),
            ],
            [
                'first_name' => 'Sara',
                'last_name' => 'Al-Otaibi',
                'mobile' => '+966501000002',
                'email' => 'sara.otaibi@example.com',
                'gender' => 'female',
                'nationality' => 'Saudi Arabia',
                'member_role_id' => $roles['specialist'] ?? $roles->first(),
                'specialty_id' => $specialties->skip(1)->first() ?? $specialties->first(),
            ],
            [
                'first_name' => 'Khalid',
                'last_name' => 'Al-Mutairi',
                'mobile' => '+966501000003',
                'email' => 'khalid.mutairi@example.com',
                'gender' => 'male',
                'nationality' => 'Saudi Arabia',
                'member_role_id' => $roles['coordinator'] ?? $roles->first(),
                'specialty_id' => null,
            ],
            [
                'first_name' => 'Noura',
                'last_name' => 'Al-Ghamdi',
                'mobile' => '+966501000004',
                'email' => 'noura.ghamdi@example.com',
                'gender' => 'female',
                'nationality' => 'Saudi Arabia',
                'member_role_id' => $roles['attendance_officer'] ?? $roles->first(),
                'specialty_id' => null,
            ],
            [
                'first_name' => 'Faisal',
                'last_name' => 'Al-Zahrani',
                'mobile' => '+966501000005',
                'email' => 'faisal.zahrani@example.com',
                'gender' => 'male',
                'nationality' => 'Saudi Arabia',
                'member_role_id' => $roles['transportation_officer'] ?? $roles->first(),
                'specialty_id' => null,
            ],
        ];

        foreach ($samples as $sample) {
            Member::query()->updateOrCreate(
                ['mobile' => $sample['mobile']],
                [
                    ...$sample,
                    'full_name' => trim($sample['first_name'].' '.$sample['last_name']),
                    'status' => MemberStatus::Active->value,
                ]
            );
        }
    }
}
