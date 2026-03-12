<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InAppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ALOS-S1-26 — In-app notifications: list, mark as read (tenant-scoped, current user only).
 * Used from admin (admin.core.notifications) and from tenant office (company.notifications).
 */
class NotificationController extends Controller
{
    protected function notificationsRoutePrefix(): string
    {
        $name = request()->route()?->getName() ?? '';
        return str_starts_with($name, 'company.') ? 'company.notifications' : 'admin.core.notifications';
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $notifications = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);

        if ($user instanceof \App\Models\User) {
            $query = InAppNotification::forUser($user->id)
                ->forTenant($user->tenant_id)
                ->orderByDesc('created_at');

            if ($request->filled('type')) {
                $query->where('type', $request->get('type'));
            }
            if ($request->filled('unread')) {
                $query->unread();
            }

            $perPage = (int) $request->get('per_page', 20);
            $perPage = in_array($perPage, [10, 20, 50], true) ? $perPage : 20;
            $notifications = $query->paginate($perPage)->withQueryString();
        }

        $prefix = $this->notificationsRoutePrefix();
        $pageConfigs = str_starts_with($prefix, 'company.') ? ['myLayout' => 'office', 'customizerHide' => true] : [];

        return view('core::content.notifications.index', [
            'notifications' => $notifications,
            'isOfficeUser' => $user instanceof \App\Models\User,
            'notificationsRoutePrefix' => $prefix,
            'pageConfigs' => $pageConfigs,
        ]);
    }

    public function markAsRead(int $notification): RedirectResponse
    {
        $user = auth()->user();
        if (! $user instanceof \App\Models\User) {
            abort(403);
        }
        $n = InAppNotification::where('id', $notification)->firstOrFail();
        if ($n->user_id !== $user->id || ($user->tenant_id && $n->tenant_id !== $user->tenant_id)) {
            abort(404);
        }
        $n->markAsRead();
        return redirect()->back()->with('success', __('Marked as read.'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (! $user instanceof \App\Models\User) {
            abort(403);
        }
        InAppNotification::forUser($user->id)
            ->forTenant($user->tenant_id)
            ->unread()
            ->update(['read_status' => true, 'read_at' => now()]);
        return redirect()->back()->with('success', __('All marked as read.'));
    }
}
