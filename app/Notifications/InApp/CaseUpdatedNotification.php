<?php

namespace App\Notifications\InApp;

use App\Models\CaseModel;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\App;

/**
 * ALOS-S1-26 — Case updated: notify team and optionally client portal user.
 */
class CaseUpdatedNotification
{
    public static function send(CaseModel $case, string $summary = ''): void
    {
        $client = $case->client;
        $tenantId = $case->tenant_id;
        $title = __('Case updated');
        $messageBody = $summary ?: __('Case :number has been updated.', ['number' => $case->case_number]);
        $data = [
            'link' => route('admin.core.cases.show', $case),
            'entity_type' => 'case',
            'entity_id' => $case->id,
            'client_id' => $client->id,
        ];
        $service = App::make(InAppNotificationService::class);
        $userIds = $client->teamAccess()->pluck('id')->toArray();
        $service->notifyMany($userIds, \App\Models\InAppNotification::TYPE_CASE_UPDATED, $title, $messageBody, $tenantId, $data);
        $portalUser = $client->portalUser;
        if ($portalUser) {
            // البوابة لا تحتوي صفحة قضية؛ نربط بصفحة ذات صلة (استشارات أو تقارير)
            $data['link'] = route('portal.consultations.index');
            $service->notify($portalUser->id, \App\Models\InAppNotification::TYPE_CASE_UPDATED, $title, $messageBody, $tenantId, $data);
        }
    }
}
