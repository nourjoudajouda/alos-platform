<?php

namespace App\Notifications\InApp;

use App\Models\Client;
use App\Models\Document;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\App;

/**
 * ALOS-S1-26 — Document shared with client: notify client portal user (and optionally team).
 */
class DocumentSharedNotification
{
    public static function send(Document $document): void
    {
        if ($document->visibility !== Document::VISIBILITY_SHARED) {
            return;
        }
        $client = $document->client;
        $tenantId = $client->tenant_id;

        $title = __('Document shared');
        $messageBody = __('A new document has been shared with you: :name', ['name' => $document->file_name ?? __('Document')]);
        $linkPortal = route('portal.documents.index');
        $linkOffice = route('admin.core.clients.documents.index', $client);
        $data = [
            'link' => $linkPortal,
            'entity_type' => 'document',
            'entity_id' => $document->id,
            'client_id' => $client->id,
        ];

        $service = App::make(InAppNotificationService::class);

        $portalUser = $client->portalUser;
        if ($portalUser) {
            $service->notify($portalUser->id, \App\Models\InAppNotification::TYPE_DOCUMENT_SHARED, $title, $messageBody, $tenantId, $data);
        }

        $data['link'] = $linkOffice;
        $userIds = $client->teamAccess()->pluck('id')->toArray();
        $service->notifyMany($userIds, \App\Models\InAppNotification::TYPE_DOCUMENT_SHARED, $title, $messageBody, $tenantId, $data);
    }
}
