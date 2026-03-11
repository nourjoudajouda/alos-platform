<?php

namespace App\Notifications\InApp;

use App\Models\Consultation;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\App;

/**
 * ALOS-S1-26 — Consultation created or updated: notify client team and optionally portal user.
 */
class ConsultationNotification
{
    public static function send(Consultation $consultation, bool $isNew = true): void
    {
        $client = $consultation->client;
        $tenantId = $consultation->tenant_id;

        $title = $isNew ? __('New consultation') : __('Consultation updated');
        $messageBody = $isNew
            ? __('A new consultation was added: :title', ['title' => $consultation->title])
            : __('Consultation ":title" was updated.', ['title' => $consultation->title]);
        $data = [
            'link' => route('admin.core.consultations.show', $consultation),
            'entity_type' => 'consultation',
            'entity_id' => $consultation->id,
            'client_id' => $client->id,
        ];

        $service = App::make(InAppNotificationService::class);

        $userIds = $client->teamAccess()->pluck('id')->toArray();
        $service->notifyMany($userIds, \App\Models\InAppNotification::TYPE_CONSULTATION, $title, $messageBody, $tenantId, $data);

        $portalUser = $client->portalUser;
        if ($portalUser) {
            $data['link'] = route('portal.consultations.show', ['consultation' => $consultation]);
            $service->notify($portalUser->id, \App\Models\InAppNotification::TYPE_CONSULTATION, $title, $messageBody, $tenantId, $data);
        }
    }
}
