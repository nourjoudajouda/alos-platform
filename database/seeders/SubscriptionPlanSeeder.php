<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * ALOS-S1-29 — Subscription Plans Example: Basic, Professional, Enterprise.
 * Limits: lawyers, admins, secretaries (stored in features_json['limits']), storage in MB, user_limit.
 */
class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'plan_name' => 'Basic',
                'price' => 99.00,
                'user_limit' => 5,
                'lawyer_limit' => 3,
                'storage_limit' => 20 * 1024, // 20 GB in MB
                'features_json' => [
                    'case_management' => true,
                    'client_portal' => true,
                    'internal_chat' => true,
                    'calendar' => true,
                    'reports' => false,
                    'finance_module' => false,
                    'hr_module' => false,
                    'marketplace' => false,
                    'advanced_security' => false,
                    'api_access' => false,
                    'custom_integrations' => false,
                    'limits' => [
                        'max_admins' => 1,
                        'max_secretaries' => 1,
                        'max_accountants' => 1,
                        'max_trainees' => 1,
                        'max_clients' => 100,
                        'max_cases' => 100,
                        'max_documents' => 500,
                    ],
                ],
            ],
            [
                'plan_name' => 'Professional',
                'price' => 249.00,
                'user_limit' => 15,
                'lawyer_limit' => 10,
                'storage_limit' => 100 * 1024, // 100 GB in MB
                'features_json' => [
                    'case_management' => true,
                    'client_portal' => true,
                    'internal_chat' => true,
                    'calendar' => true,
                    'reports' => true,
                    'finance_module' => true,
                    'hr_module' => true,
                    'marketplace' => false,
                    'advanced_security' => false,
                    'api_access' => false,
                    'custom_integrations' => false,
                    'limits' => [
                        'max_admins' => 2,
                        'max_secretaries' => 3,
                        'max_accountants' => 2,
                        'max_trainees' => 2,
                        'max_clients' => 500,
                        'max_cases' => 0, // Unlimited
                        'max_documents' => 2000,
                    ],
                ],
            ],
            [
                'plan_name' => 'Enterprise',
                'price' => 599.00,
                'user_limit' => 0, // Unlimited
                'lawyer_limit' => 0, // Unlimited
                'storage_limit' => 1024 * 1024, // 1 TB in MB
                'features_json' => [
                    'case_management' => true,
                    'client_portal' => true,
                    'internal_chat' => true,
                    'calendar' => true,
                    'reports' => true,
                    'finance_module' => true,
                    'hr_module' => true,
                    'marketplace' => true,
                    'advanced_security' => true,
                    'api_access' => true,
                    'custom_integrations' => true,
                    'limits' => [
                        'max_admins' => 0,
                        'max_secretaries' => 0,
                        'max_accountants' => 0,
                        'max_trainees' => 0,
                        'max_clients' => 0,
                        'max_cases' => 0,
                        'max_documents' => 0,
                    ], // 0 = unlimited
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['plan_name' => $plan['plan_name']],
                $plan
            );
        }
    }
}
