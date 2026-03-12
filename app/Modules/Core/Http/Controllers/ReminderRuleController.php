<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReminderRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-13 — Session reminder rules (tenant-scoped).
 * Used only under /company (Tenant Office); not in Platform Admin.
 */
class ReminderRuleController extends Controller
{
    protected function tenantId(): ?int
    {
        $user = auth()->user();
        if (! $user || ! $user->tenant_id) {
            return null;
        }
        return (int) $user->tenant_id;
    }

    protected function routePrefix(): string
    {
        return 'company.reminder-rules';
    }

    public function index(Request $request): View
    {
        $tenantId = $this->tenantId();
        if ($tenantId === null) {
            abort(403, __('You must belong to a law firm to manage reminder rules.'));
        }

        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $query = ReminderRule::query()->where('tenant_id', $tenantId)->orderBy('sort_order')->orderBy('id');
        $rules = $query->paginate($perPage)->withQueryString();

        $activeCount = ReminderRule::where('tenant_id', $tenantId)->where('active', true)->count();
        $totalCount = ReminderRule::where('tenant_id', $tenantId)->count();

        return view('core::content.reminder-rules.index', [
            'rules' => $rules,
            'perPage' => $perPage,
            'activeCount' => $activeCount,
            'totalCount' => $totalCount,
            'reminderRulesRoutePrefix' => $this->routePrefix(),
            'pageConfigs' => ['myLayout' => 'office', 'customizerHide' => true],
        ]);
    }

    public function create(): View
    {
        if ($this->tenantId() === null) {
            abort(403, __('You must belong to a law firm to manage reminder rules.'));
        }
        return view('core::content.reminder-rules.create', [
            'reminderRulesRoutePrefix' => $this->routePrefix(),
            'pageConfigs' => ['myLayout' => 'office', 'customizerHide' => true],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = $this->tenantId();
        if ($tenantId === null) {
            abort(403, __('You must belong to a law firm to manage reminder rules.'));
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:64'],
            'trigger_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'channel_database' => ['boolean'],
            'channel_mail' => ['boolean'],
            'notify_client' => ['boolean'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['channel_database'] = $request->boolean('channel_database');
        $validated['channel_mail'] = $request->boolean('channel_mail');
        $validated['notify_client'] = $request->boolean('notify_client');
        $validated['active'] = $request->boolean('active');
        $maxOrder = ReminderRule::where('tenant_id', $tenantId)->max('sort_order');
        $validated['sort_order'] = $validated['sort_order'] ?? (($maxOrder ?? 0) + 1);

        ReminderRule::create($validated);

        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', __('Reminder rule created successfully.'));
    }

    public function edit(ReminderRule $reminderRule): View
    {
        $tenantId = $this->tenantId();
        if ($tenantId === null || $reminderRule->tenant_id !== $tenantId) {
            abort(404);
        }
        return view('core::content.reminder-rules.edit', [
            'rule' => $reminderRule,
            'reminderRulesRoutePrefix' => $this->routePrefix(),
            'pageConfigs' => ['myLayout' => 'office', 'customizerHide' => true],
        ]);
    }

    public function update(Request $request, ReminderRule $reminderRule): RedirectResponse
    {
        $tenantId = $this->tenantId();
        if ($tenantId === null || $reminderRule->tenant_id !== $tenantId) {
            abort(404);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:64'],
            'trigger_minutes' => ['required', 'integer', 'min:1', 'max:525600'],
            'channel_database' => ['boolean'],
            'channel_mail' => ['boolean'],
            'notify_client' => ['boolean'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['channel_database'] = $request->boolean('channel_database');
        $validated['channel_mail'] = $request->boolean('channel_mail');
        $validated['notify_client'] = $request->boolean('notify_client');
        $validated['active'] = $request->boolean('active');

        $reminderRule->update($validated);

        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', __('Reminder rule updated successfully.'));
    }

    public function destroy(ReminderRule $reminderRule): RedirectResponse
    {
        $tenantId = $this->tenantId();
        if ($tenantId === null || $reminderRule->tenant_id !== $tenantId) {
            abort(404);
        }
        $reminderRule->delete();

        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', __('Reminder rule deleted successfully.'));
    }
}
