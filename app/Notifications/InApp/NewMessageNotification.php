<?php

namespace App\Notifications\InApp;

use App\Models\Client;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use App\Services\InAppNotificationService;
use Illuminate\Support\Facades\App;

/**
 * ALOS-S1-26 — New message in thread: notify recipients (office team or client).
 */
class NewMessageNotification
{
    public static function send(MessageThread $thread, Message $message): void
    {
        $client = $thread->client;
        $tenantId = $client->tenant_id;
        $senderName = $message->user?->name ?? __('Someone');

        $title = __('New message');
        $messageBody = __(':name sent a message in ":subject"', [
            'name' => $senderName,
            'subject' => $thread->subject,
        ]);
        $linkOffice = route('admin.core.clients.threads.show', [$client, $thread]);
        $data = [
            'link' => $linkOffice,
            'entity_type' => 'message_thread',
            'entity_id' => $thread->id,
            'client_id' => $client->id,
        ];

        $service = App::make(InAppNotificationService::class);

        if ($message->user && $message->user->isClientPortalUser()) {
            // Message from client → notify office team
            $userIds = $client->teamAccess()->pluck('id')->toArray();
            $service->notifyMany($userIds, \App\Models\InAppNotification::TYPE_NEW_MESSAGE, $title, $messageBody, $tenantId, $data);
        } else {
            // Message from office → notify client portal user
            $portalUser = $client->portalUser;
            if ($portalUser) {
                $data['link'] = route('portal.messages.show', ['thread' => $thread]);
                $service->notify($portalUser->id, \App\Models\InAppNotification::TYPE_NEW_MESSAGE, $title, $messageBody, $tenantId, $data);
            }
        }
    }
}
