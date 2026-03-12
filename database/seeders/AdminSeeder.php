<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ALOS — مستخدمون افتراضيون للتطوير والدخول.
 * - جدول admins: تسجيل الدخول من /admin/login (لوحة الإدارة العليا).
 * - جدول users + tenants: يوزرز المكاتب والتسجيل من /login.
 * تشغيل: php artisan db:seed --class=AdminSeeder
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // أدمن لوحة الإدارة (جدول admins / SYSTEMADMIN) — الدخول من /admin/login
        Admin::firstOrCreate(
            ['email' => 'admin@alos.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

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
