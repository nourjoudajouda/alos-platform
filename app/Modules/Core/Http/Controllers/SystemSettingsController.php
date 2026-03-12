<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SystemSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * ALOS-S1-30 — System Settings & Global Configuration.
 * Only platform admins (auth:admin) may access. Tenant admins must not see this.
 */
class SystemSettingsController extends Controller
{
    public function __construct(
        private SystemSettingsService $settings
    ) {}

    /**
     * Display system settings page with tabs.
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'general');
        $allowedTabs = ['general', 'mail', 'notifications', 'storage', 'registration', 'branding'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'general';
        }

        $general = $this->settings->getGroup('general');
        $mail = $this->settings->getGroup('mail');
        $notifications = $this->settings->getGroup('notifications');
        $storage = $this->settings->getGroup('storage');
        $registration = $this->settings->getGroup('registration');
        $branding = $this->settings->getGroup('branding');

        $plans = SubscriptionPlan::orderBy('price')->get();

        return view('core::content.system-settings.index', [
            'activeTab' => $tab,
            'general' => $general,
            'mail' => $mail,
            'notifications' => $notifications,
            'storage' => $storage,
            'registration' => $registration,
            'branding' => $branding,
            'subscriptionPlans' => $plans,
        ]);
    }

    /**
     * Update a settings group. Only non-password mail fields updated from request; password only if provided.
     */
    public function update(Request $request): RedirectResponse
    {
        $group = $request->validate(['group' => ['required', 'string', 'in:general,mail,notifications,storage,registration,branding']])['group'];

        if ($group === 'general') {
            $validated = $request->validate([
                'system_name' => ['nullable', 'string', 'max:128'],
                'system_logo' => ['nullable', 'string', 'max:500'],
                'favicon' => ['nullable', 'string', 'max:500'],
                'support_email' => ['nullable', 'email'],
                'support_phone' => ['nullable', 'string', 'max:64'],
                'default_language' => ['nullable', 'string', 'max:16'],
                'timezone' => ['nullable', 'string', 'max:64'],
            ]);
            foreach ($validated as $key => $value) {
                $this->settings->set($key, $value ?? '', 'string', 'general');
            }
        }

        if ($group === 'mail') {
            $rules = [
                'mail_driver' => ['nullable', 'string', 'max:32'],
                'mail_host' => ['nullable', 'string', 'max:255'],
                'mail_port' => ['nullable', 'string', 'max:8'],
                'mail_username' => ['nullable', 'string', 'max:255'],
                'mail_encryption' => ['nullable', 'string', 'max:16'],
                'mail_from_address' => ['nullable', 'email'],
                'mail_from_name' => ['nullable', 'string', 'max:128'],
            ];
            $rules['mail_password'] = ['nullable', 'string', 'max:255'];
            $validated = $request->validate($rules);
            foreach ($validated as $key => $value) {
                if ($key === 'mail_password' && $value === '') {
                    continue; // do not overwrite with empty
                }
                $this->settings->set($key, $value ?? '', 'string', 'mail');
            }
        }

        if ($group === 'notifications') {
            $request->validate([
                'enable_in_app_notifications' => ['nullable'],
                'enable_email_notifications' => ['nullable'],
                'enable_session_reminders' => ['nullable'],
                'enable_auto_reports' => ['nullable'],
            ]);
            $this->settings->set('enable_in_app_notifications', $request->boolean('enable_in_app_notifications'), 'boolean', 'notifications');
            $this->settings->set('enable_email_notifications', $request->boolean('enable_email_notifications'), 'boolean', 'notifications');
            $this->settings->set('enable_session_reminders', $request->boolean('enable_session_reminders'), 'boolean', 'notifications');
            $this->settings->set('enable_auto_reports', $request->boolean('enable_auto_reports'), 'boolean', 'notifications');
        }

        if ($group === 'storage') {
            $validated = $request->validate([
                'storage_driver' => ['nullable', 'string', 'max:32'],
                'max_upload_size' => ['nullable', 'integer', 'min:1', 'max:512000'],
                'allowed_file_types' => ['nullable', 'string', 'max:255'],
                'public_storage_disk' => ['nullable', 'string', 'max:32'],
                'private_storage_disk' => ['nullable', 'string', 'max:32'],
            ]);
            foreach ($validated as $key => $value) {
                $this->settings->set($key, $value ?? '', $key === 'max_upload_size' ? 'integer' : 'string', 'storage');
            }
        }

        if ($group === 'registration') {
            $validated = $request->validate([
                'allow_tenant_registration' => ['nullable'],
                'require_email_verification' => ['nullable'],
                'default_subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
                'allow_trial_accounts' => ['nullable'],
            ]);
            $this->settings->set('allow_tenant_registration', $request->boolean('allow_tenant_registration'), 'boolean', 'registration');
            $this->settings->set('require_email_verification', $request->boolean('require_email_verification'), 'boolean', 'registration');
            $this->settings->set('default_subscription_plan_id', $validated['default_subscription_plan_id'] ?? null, 'string', 'registration');
            $this->settings->set('allow_trial_accounts', $request->boolean('allow_trial_accounts'), 'boolean', 'registration');
        }

        if ($group === 'branding') {
            $validated = $request->validate([
                'system_logo' => ['nullable', 'string', 'max:500'],
                'favicon' => ['nullable', 'string', 'max:500'],
            ]);
            $this->settings->set('system_logo', $validated['system_logo'] ?? '', 'string', 'general');
            $this->settings->set('favicon', $validated['favicon'] ?? '', 'string', 'general');
        }

        return redirect()
            ->route('admin.core.system-settings.index', ['tab' => $group])
            ->with('success', __('Settings saved successfully.'));
    }

    /**
     * Send a test email. Uses saved system mail settings if present; otherwise Laravel config.
     */
    public function testMail(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $to = $request->input('email');
        $mail = $this->settings->getGroup('mail');

        if (! empty($mail['mail_host'])) {
            $this->applyMailConfigFromSettings($mail);
        }

        try {
            Mail::raw(__('This is a test email from ALOS system settings.'), function ($message) use ($to) {
                $message->to($to)->subject(__('ALOS Test Email'));
            });
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.core.system-settings.index', ['tab' => 'mail'])
                ->with('error', __('Failed to send test email: :message', ['message' => $e->getMessage()]));
        }

        return redirect()
            ->route('admin.core.system-settings.index', ['tab' => 'mail'])
            ->with('success', __('Test email sent to :email.', ['email' => $to]));
    }

    private function applyMailConfigFromSettings(array $mail): void
    {
        Config::set('mail.default', $mail['mail_driver'] ?? 'smtp');
        Config::set('mail.mailers.smtp.host', $mail['mail_host'] ?? '');
        Config::set('mail.mailers.smtp.port', (int) ($mail['mail_port'] ?? 587));
        Config::set('mail.mailers.smtp.username', $mail['mail_username'] ?? '');
        Config::set('mail.mailers.smtp.password', $mail['mail_password'] ?? '');
        Config::set('mail.mailers.smtp.encryption', $mail['mail_encryption'] ?? 'tls');
        Config::set('mail.from.address', $mail['mail_from_address'] ?? config('mail.from.address'));
        Config::set('mail.from.name', $mail['mail_from_name'] ?? config('mail.from.name'));
    }
}
