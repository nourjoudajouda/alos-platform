<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-26 — Client portal: current user's notifications only.
 */
class PortalNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = InAppNotification::forUser($user->id)
            ->forTenant($user->tenant_id)
            ->orderByDesc('created_at');
        if ($request->filled('unread')) {
            $query->unread();
        }
        $perPage = (int) $request->get('per_page', 20);
        $notifications = $query->paginate(min(max($perPage, 10), 50))->withQueryString();
        return view('portal.notifications.index', ['notifications' => $notifications]);
    }

    public function markAsRead(int $notification): RedirectResponse
    {
        $user = auth()->user();
        $n = InAppNotification::where('id', $notification)->firstOrFail();
        if ($n->user_id !== $user->id || ($user->tenant_id && $n->tenant_id !== $user->tenant_id)) {
            abort(404);
        }
        $n->markAsRead();
        return redirect()->back()->with('success', __('Marked as read.'));
    }

    public function markAllAsRead(): RedirectResponse
    {
        $user = auth()->user();
        InAppNotification::forUser($user->id)->forTenant($user->tenant_id)->unread()
            ->update(['read_status' => true, 'read_at' => now()]);
        return redirect()->back()->with('success', __('All marked as read.'));
    }
}
