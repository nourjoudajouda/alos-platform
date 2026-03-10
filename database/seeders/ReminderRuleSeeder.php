<?php

namespace Database\Seeders;

use App\Models\ReminderRule;
use Illuminate\Database\Seeder;

/**
 * ALOS-S1-13 — Default reminder rules: 7 days, 24 hours, 2 hours before session.
 */
class ReminderRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'tenant_id' => null,
                'label' => '7 أيام قبل الجلسة',
                'trigger_minutes' => 7 * 24 * 60, // 10080
                'channel_database' => true,
                'channel_mail' => true,
                'notify_client' => false,
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'tenant_id' => null,
                'label' => '24 ساعة قبل الجلسة',
                'trigger_minutes' => 24 * 60, // 1440
                'channel_database' => true,
                'channel_mail' => true,
                'notify_client' => false,
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'tenant_id' => null,
                'label' => 'ساعتان قبل الجلسة',
                'trigger_minutes' => 2 * 60, // 120
                'channel_database' => true,
                'channel_mail' => true,
                'notify_client' => false,
                'active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($rules as $attrs) {
            ReminderRule::firstOrCreate(
                [
                    'tenant_id' => $attrs['tenant_id'],
                    'trigger_minutes' => $attrs['trigger_minutes'],
                ],
                $attrs
            );
        }
    }
}
