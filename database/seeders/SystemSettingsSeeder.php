<?php

namespace Database\Seeders;

use App\Services\SystemSettingsService;
use Illuminate\Database\Seeder;

/**
 * ALOS-S1-30 — Default platform system settings.
 * Run: php artisan db:seed --class=SystemSettingsSeeder
 */
class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(SystemSettingsService::class);

        $defaults = [
            // General / Identity
            ['key' => 'system_name', 'value' => config('app.name', 'ALOS'), 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'system_logo', 'value' => null, 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'favicon', 'value' => null, 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'support_email', 'value' => config('mail.from.address', 'support@alos.local'), 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'support_phone', 'value' => null, 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'default_language', 'value' => config('app.locale', 'en'), 'type' => 'string', 'group_name' => 'general'],
            ['key' => 'timezone', 'value' => config('app.timezone', 'UTC'), 'type' => 'string', 'group_name' => 'general'],

            // Mail (override .env in DB for admin UI)
            ['key' => 'mail_driver', 'value' => config('mail.default', 'smtp'), 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_host', 'value' => config('mail.mailers.smtp.host', ''), 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_port', 'value' => (string) (config('mail.mailers.smtp.port') ?? 587), 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_username', 'value' => config('mail.mailers.smtp.username', ''), 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_encryption', 'value' => config('mail.mailers.smtp.encryption', 'tls'), 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_from_address', 'value' => config('mail.from.address', ''), 'type' => 'string', 'group_name' => 'mail'],
            ['key' => 'mail_from_name', 'value' => config('mail.from.name', 'ALOS'), 'type' => 'string', 'group_name' => 'mail'],

            // Notifications
            ['key' => 'enable_in_app_notifications', 'value' => '1', 'type' => 'boolean', 'group_name' => 'notifications'],
            ['key' => 'enable_email_notifications', 'value' => '1', 'type' => 'boolean', 'group_name' => 'notifications'],
            ['key' => 'enable_session_reminders', 'value' => '1', 'type' => 'boolean', 'group_name' => 'notifications'],
            ['key' => 'enable_auto_reports', 'value' => '0', 'type' => 'boolean', 'group_name' => 'notifications'],

            // Storage
            ['key' => 'storage_driver', 'value' => config('filesystems.default', 'local'), 'type' => 'string', 'group_name' => 'storage'],
            ['key' => 'max_upload_size', 'value' => '10240', 'type' => 'integer', 'group_name' => 'storage'], // KB
            ['key' => 'allowed_file_types', 'value' => 'pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif', 'type' => 'string', 'group_name' => 'storage'],
            ['key' => 'public_storage_disk', 'value' => 'public', 'type' => 'string', 'group_name' => 'storage'],
            ['key' => 'private_storage_disk', 'value' => 'local', 'type' => 'string', 'group_name' => 'storage'],

            // Registration
            ['key' => 'allow_tenant_registration', 'value' => '1', 'type' => 'boolean', 'group_name' => 'registration'],
            ['key' => 'require_email_verification', 'value' => '0', 'type' => 'boolean', 'group_name' => 'registration'],
            ['key' => 'default_subscription_plan_id', 'value' => null, 'type' => 'string', 'group_name' => 'registration'],
            ['key' => 'allow_trial_accounts', 'value' => '1', 'type' => 'boolean', 'group_name' => 'registration'],
        ];

        foreach ($defaults as $item) {
            $service->set(
                $item['key'],
                $item['value'],
                $item['type'],
                $item['group_name']
            );
        }
    }
}
