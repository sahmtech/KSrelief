<?php

namespace Database\Seeders;

use App\Models\MemberRole;
use Illuminate\Database\Seeder;

class MemberRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Coordinator', 'code' => 'coordinator'],
            ['name' => 'Doctor', 'code' => 'doctor'],
            ['name' => 'Specialist', 'code' => 'specialist'],
            ['name' => 'Technician', 'code' => 'technician'],
            ['name' => 'Attendance Officer', 'code' => 'attendance_officer'],
            ['name' => 'Transportation Officer', 'code' => 'transportation_officer'],
        ];

        foreach ($roles as $role) {
            MemberRole::query()->updateOrCreate(
                ['code' => $role['code']],
                [
                    'name' => $role['name'],
                    'status' => 'active',
                ]
            );
        }
    }
}
