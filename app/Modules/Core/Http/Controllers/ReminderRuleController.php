<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReminderRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-13 — Admin UI for session reminder rules.
 */
class ReminderRuleController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = (int) $request->get('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $rules = ReminderRule::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        $activeCount = ReminderRule::where('active', true)->count();
        $totalCount = ReminderRule::count();

        return view('core::content.reminder-rules.index', [
            'rules' => $rules,
            'perPage' => $perPage,
            'activeCount' => $activeCount,
            'totalCount' => $totalCount,
        ]);
    }

    public function create(): View
    {
        return view('core::content.reminder-rules.create');
    }

    public function store(Request $request): RedirectResponse
    {
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
        $validated['sort_order'] = $validated['sort_order'] ?? ReminderRule::max('sort_order') + 1;

        ReminderRule::create($validated);

        return redirect()
            ->route('admin.core.reminder-rules.index')
            ->with('success', __('Reminder rule created successfully.'));
    }

    public function edit(ReminderRule $reminderRule): View
    {
        return view('core::content.reminder-rules.edit', [
            'rule' => $reminderRule,
        ]);
    }

    public function update(Request $request, ReminderRule $reminderRule): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:64'],
            'trigger_minutes' => ['required', 'integer', 'min:1', 'max:525600'], // max ~1 year
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
            ->route('admin.core.reminder-rules.index')
            ->with('success', __('Reminder rule updated successfully.'));
    }

    public function destroy(ReminderRule $reminderRule): RedirectResponse
    {
        $reminderRule->delete();

        return redirect()
            ->route('admin.core.reminder-rules.index')
            ->with('success', __('Reminder rule deleted successfully.'));
    }
}
