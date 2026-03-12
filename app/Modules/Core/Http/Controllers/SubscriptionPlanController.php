<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SubscriptionPlan;
use App\Services\AuditLogService;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-29 — Admin CRUD for Subscription Plans.
 */
class SubscriptionPlanController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $plans = SubscriptionPlan::query()
            ->orderBy('price')
            ->paginate($perPage)
            ->withQueryString();

        $totalPlans = SubscriptionPlan::count();

        return view('core::content.subscription-plans.index', [
            'plans' => $plans,
            'perPage' => $perPage,
            'totalPlans' => $totalPlans,
        ]);
    }

    public function create(): View
    {
        return view('core::content.subscription-plans.create', [
            'featureOptions' => $this->getFeatureOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'user_limit' => ['required', 'integer', 'min:0'],
            'lawyer_limit' => ['required', 'integer', 'min:0'],
            'storage_limit' => ['required', 'integer', 'min:0'],
            'features_json' => ['nullable', 'string'],
        ]);

        $features = $this->buildFeaturesFromRequest($request);
        $validated['features_json'] = $features;

        $plan = SubscriptionPlan::create($validated);
        app(AuditLogService::class)->recordPlatformAudit(
            AuditLog::ACTION_CREATE_SUBSCRIPTION_PLAN,
            AuditLog::ENTITY_SUBSCRIPTION_PLAN,
            $plan->id,
            [],
            $plan->only(['plan_name', 'price', 'user_limit', 'lawyer_limit', 'storage_limit']),
            null
        );

        return redirect()
            ->route('admin.core.subscription-plans.index')
            ->with('success', __('Subscription plan created successfully.'));
    }

    public function edit(SubscriptionPlan $subscription_plan): View
    {
        return view('core::content.subscription-plans.edit', [
            'plan' => $subscription_plan,
            'featureOptions' => $this->getFeatureOptions(),
        ]);
    }

    public function update(Request $request, SubscriptionPlan $subscription_plan): RedirectResponse
    {
        $validated = $request->validate([
            'plan_name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'user_limit' => ['required', 'integer', 'min:0'],
            'lawyer_limit' => ['required', 'integer', 'min:0'],
            'storage_limit' => ['required', 'integer', 'min:0'],
            'features_json' => ['nullable', 'string'],
        ]);

        $features = $this->buildFeaturesFromRequest($request);
        $validated['features_json'] = $features;

        $oldValues = $subscription_plan->only(['plan_name', 'price', 'user_limit', 'lawyer_limit', 'storage_limit']);
        $subscription_plan->update($validated);
        app(AuditLogService::class)->recordPlatformAudit(
            AuditLog::ACTION_UPDATE_SUBSCRIPTION_PLAN,
            AuditLog::ENTITY_SUBSCRIPTION_PLAN,
            $subscription_plan->id,
            $oldValues,
            $subscription_plan->only(['plan_name', 'price', 'user_limit', 'lawyer_limit', 'storage_limit']),
            null
        );

        return redirect()
            ->route('admin.core.subscription-plans.index')
            ->with('success', __('Subscription plan updated successfully.'));
    }

    public function destroy(SubscriptionPlan $subscription_plan): RedirectResponse
    {
        if ($subscription_plan->tenants()->exists()) {
            return redirect()
                ->route('admin.core.subscription-plans.index')
                ->with('error', __('Cannot delete a plan that is assigned to one or more tenants.'));
        }

        $oldValues = $subscription_plan->only(['plan_name', 'price', 'user_limit', 'lawyer_limit', 'storage_limit']);
        $planId = $subscription_plan->id;
        $subscription_plan->delete();
        app(AuditLogService::class)->recordPlatformAudit(
            AuditLog::ACTION_DELETE_SUBSCRIPTION_PLAN,
            AuditLog::ENTITY_SUBSCRIPTION_PLAN,
            $planId,
            $oldValues,
            [],
            null
        );

        return redirect()
            ->route('admin.core.subscription-plans.index')
            ->with('success', __('Subscription plan deleted successfully.'));
    }

    /** @return array<string, string> feature_key => label */
    private function getFeatureOptions(): array
    {
        return [
            PlanLimitService::FEATURE_CASE_MANAGEMENT => __('Case management'),
            PlanLimitService::FEATURE_CLIENT_PORTAL => __('Client portal'),
            PlanLimitService::FEATURE_INTERNAL_CHAT => __('Internal chat / Messaging'),
            PlanLimitService::FEATURE_CALENDAR => __('Calendar / Sessions'),
            PlanLimitService::FEATURE_REPORTS => __('Reports'),
            PlanLimitService::FEATURE_FINANCE_MODULE => __('Finance module'),
            PlanLimitService::FEATURE_HR_MODULE => __('HR module'),
            PlanLimitService::FEATURE_MARKETPLACE => __('Marketplace'),
            PlanLimitService::FEATURE_ADVANCED_SECURITY => __('Advanced security'),
            PlanLimitService::FEATURE_API_ACCESS => __('API access'),
            PlanLimitService::FEATURE_CUSTOM_INTEGRATIONS => __('Custom integrations'),
        ];
    }

    /**
     * Build features_json array from request: checkboxes (feature_*) and optional raw JSON.
     */
    private function buildFeaturesFromRequest(Request $request): array
    {
        $known = [
            PlanLimitService::FEATURE_CASE_MANAGEMENT,
            PlanLimitService::FEATURE_CLIENT_PORTAL,
            PlanLimitService::FEATURE_INTERNAL_CHAT,
            PlanLimitService::FEATURE_CALENDAR,
            PlanLimitService::FEATURE_REPORTS,
            PlanLimitService::FEATURE_FINANCE_MODULE,
            PlanLimitService::FEATURE_HR_MODULE,
            PlanLimitService::FEATURE_MARKETPLACE,
            PlanLimitService::FEATURE_ADVANCED_SECURITY,
            PlanLimitService::FEATURE_API_ACCESS,
            PlanLimitService::FEATURE_CUSTOM_INTEGRATIONS,
        ];
        $features = [];
        foreach ($known as $key) {
            $features[$key] = $request->boolean('feature_' . $key, false);
        }
        $raw = $request->input('features_json');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $features = array_merge($features, $decoded);
            }
        }
        $features['limits'] = [
            PlanLimitService::LIMIT_MAX_ADMINS => (int) $request->input('limits_max_admins', 0),
            PlanLimitService::LIMIT_MAX_SECRETARIES => (int) $request->input('limits_max_secretaries', 0),
            PlanLimitService::LIMIT_MAX_ACCOUNTANTS => (int) $request->input('limits_max_accountants', 0),
            PlanLimitService::LIMIT_MAX_TRAINEES => (int) $request->input('limits_max_trainees', 0),
            PlanLimitService::LIMIT_MAX_CLIENTS => (int) $request->input('limits_max_clients', 0),
            PlanLimitService::LIMIT_MAX_CASES => (int) $request->input('limits_max_cases', 0),
            PlanLimitService::LIMIT_MAX_DOCUMENTS => (int) $request->input('limits_max_documents', 0),
        ];
        return $features;
    }
}
