<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ALOS — مستخدمون افتراضيون للتطوير والدخول (Office Users).
 * ALOS-S1-01: إنشاء Tenant افتراضي وربط المستخدمين به.
 * تشغيل: php artisan db:seed --class=AdminSeeder
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default']
        );

        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@alos.local',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'tenant_id' => $tenant->id,
                ]
            );
        }
    }
}
