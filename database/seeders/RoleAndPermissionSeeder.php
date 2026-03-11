<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * أدوار وصلاحيات النظام:
 * أ) مستخدمو المكتب: Admin, Managing Partner, Lawyer, Assistant, Finance
 * ب) مستخدمو العملاء: Client User
 * تشغيل: php artisan db:seed --class=RoleAndPermissionSeeder
 */
class RoleAndPermissionSeeder extends Seeder
{
    public const SYSTEM_ROLES = [
        'admin',
        'managing_partner',
        'lawyer',
        'assistant',
        'finance',
        'client_user',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $permissions = [
            'view tenants',
            'create tenants',
            'edit tenants',
            'delete tenants',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'cases.view',
            'cases.manage',
            'consultations.view',
            'consultations.manage',
            'reports.view',
            'reports.manage',
            'audit.view',
            'compliance.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        $allPermissions = Permission::all();

        // أ) مستخدمو المكتب (Internal Users)
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
        $admin->syncPermissions($allPermissions);

        $managingPartner = Role::firstOrCreate(['name' => 'managing_partner', 'guard_name' => $guard]);
        $managingPartner->syncPermissions($allPermissions);

        $lawyer = Role::firstOrCreate(['name' => 'lawyer', 'guard_name' => $guard]);
        $lawyer->syncPermissions([
            'view tenants', 'create tenants', 'edit tenants',
            'view roles', 'view permissions',
            'cases.view', 'cases.manage',
            'consultations.view', 'consultations.manage',
            'reports.view', 'reports.manage',
        ]);

        $assistant = Role::firstOrCreate(['name' => 'assistant', 'guard_name' => $guard]);
        $assistant->syncPermissions([
            'view tenants', 'create tenants', 'edit tenants',
            'view roles', 'view permissions',
            'cases.view', 'cases.manage',
            'consultations.view', 'consultations.manage',
            'reports.view', 'reports.manage',
        ]);

        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => $guard]);
        $finance->syncPermissions([
            'view tenants', 'view roles', 'view permissions',
            'cases.view',
            'consultations.view',
            'reports.view',
        ]);

        // ب) مستخدمو العملاء (External Client Users) — يرى المستخدم بيانات عميله فقط (يُطبّق لاحقاً في الواجهة)
        $clientUser = Role::firstOrCreate(['name' => 'client_user', 'guard_name' => $guard]);
        $clientUser->syncPermissions([]);

        // تعيين دور admin لمستخدم افتراضي
        $user = User::where('email', 'admin@alos.local')->first();
        if ($user && ! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
