<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Message;
use App\Notifications\InApp\NewMessageNotification;
use App\Models\MessageAttachment;
use App\Models\MessageThread;
use App\Services\AuditLogService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ALOS-S1-09 — Secure Messaging (Office side). Only team members with client_access can use.
 */
class MessageThreadController extends Controller
{
    private function authorizeClient(Client $client): void
    {
        $user = auth()->user();
        if ($user->isClientPortalUser()) {
            App::make(AuditLogService::class)->recordCompliance('access_message', __('Client portal user attempted office messaging.'), 'client', $client->id, $client->tenant_id);
            abort(403, __('Access denied. Use the client portal for messaging.'));
        }
        $hasAccess = $client->teamAccess()->where('user_id', $user->id)->exists();
        if (! $hasAccess) {
            App::make(AuditLogService::class)->recordCompliance('access_message', __('Attempted message access for client outside team.'), 'client', $client->id, $client->tenant_id);
            abort(403, __('You do not have access to this client.'));
        }
    }

    private function authorizeThread(MessageThread $thread): void
    {
        $this->authorizeClient($thread->client);
    }

    public function index(Request $request, Client $client): View
    {
        $this->authorizeClient($client);

        $query = $client->messageThreads()->with(['messages' => fn ($q) => $q->latest()->limit(1)->with('user')]);
        if ($request->boolean('archived')) {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        $threads = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        return view('core::content.clients.messages.index', [
            'client' => $client,
            'threads' => $threads,
            'showArchived' => $request->boolean('archived'),
        ]);
    }

    public function show(Client $client, MessageThread $thread): View
    {
        $this->authorizeThread($thread);
        if ($thread->client_id !== (int) $client->id) {
            abort(404);
        }

        $thread->load(['messages.user', 'messages.attachments']);

        return view('core::content.clients.messages.show', [
            'client' => $client,
            'thread' => $thread,
        ]);
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);
        $tenant = $client->tenant;
        if ($tenant) {
            try {
                app(PlanLimitService::class)->ensureFeature($tenant, PlanLimitService::FEATURE_INTERNAL_CHAT);
            } catch (\RuntimeException $e) {
                return redirect()->route('admin.core.clients.threads.index', $client)->with('error', $e->getMessage());
            }
        }

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
        ]);

        $thread = $client->messageThreads()->create([
            'subject' => $validated['subject'],
        ]);

        return redirect()
            ->route('admin.core.clients.threads.show', [$client, $thread])
            ->with('success', __('Conversation started.'));
    }

    public function storeMessage(Request $request, Client $client, MessageThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);
        $tenant = $client->tenant;
        if ($tenant) {
            try {
                app(PlanLimitService::class)->ensureFeature($tenant, PlanLimitService::FEATURE_INTERNAL_CHAT);
            } catch (\RuntimeException $e) {
                return redirect()->route('admin.core.clients.threads.show', [$client, $thread])->with('error', $e->getMessage());
            }
        }
        if ($thread->client_id !== (int) $client->id) {
            abort(404);
        }
        if ($thread->archived_at) {
            return redirect()
                ->route('admin.core.clients.threads.show', [$client, $thread])
                ->with('error', __('This conversation is archived.'));
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
            'attachments.*' => ['nullable', 'file', 'max:10240'], // 10MB per file
        ]);

        $message = $thread->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);
        $message->load('user');

        App::make(AuditLogService::class)->recordAudit(AuditLog::ACTION_SEND_MESSAGE, AuditLog::ENTITY_MESSAGE, $message->id, [], ['thread_id' => $thread->id], $client->tenant_id);
        NewMessageNotification::send($thread, $message);

        if ($request->hasFile('attachments')) {
            $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];
            $directory = "message_attachments/{$thread->id}/{$message->id}";
            $rawFiles = $_FILES['attachments'] ?? null;
            foreach ($files as $index => $file) {
                if (! $file instanceof \Illuminate\Http\UploadedFile || ! $file->isValid()) {
                    continue;
                }
                try {
                    $tmpPath = null;
                    $content = null;
                    if ($rawFiles && isset($rawFiles['tmp_name'])) {
                        $tmpName = is_array($rawFiles['tmp_name']) ? ($rawFiles['tmp_name'][$index] ?? null) : $rawFiles['tmp_name'];
                        if ($tmpName && is_uploaded_file($tmpName) && is_readable($tmpName)) {
                            $tmpPath = $tmpName;
                        }
                    }
                    if (! $tmpPath) {
                        $tmpPath = $file->getRealPath();
                        if ($tmpPath && ! is_readable($tmpPath)) {
                            $tmpPath = null;
                        }
                    }
                    if (! $tmpPath) {
                        $content = @$file->get();
                        if ($content === false || $content === '') {
                            continue;
                        }
                    }
                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName()) ?: 'file';
                    $path = $directory.'/'.uniqid().'_'.$safeName;
                    $stored = $tmpPath
                        ? Storage::disk('local')->put($path, fopen($tmpPath, 'r'))
                        : Storage::disk('local')->put($path, $content);
                    if ($stored) {
                        $size = $file->getSize();
                        if (! $size && $content !== null) {
                            $size = strlen($content);
                        }
                        $message->attachments()->create([
                            'name' => $file->getClientOriginalName(),
                            'path' => $path,
                            'disk' => 'local',
                            'size' => $size,
                            'mime_type' => $file->getMimeType(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()
            ->route('admin.core.clients.threads.show', [$client, $thread])
            ->with('success', __('Message sent.'));
    }

    public function archive(Client $client, MessageThread $thread): RedirectResponse
    {
        $this->authorizeThread($thread);
        if ($thread->client_id !== (int) $client->id) {
            abort(404);
        }

        $thread->update(['archived_at' => $thread->archived_at ? null : now()]);
        $status = $thread->archived_at ? __('archived') : __('restored');

        return redirect()
            ->route('admin.core.clients.threads.index', $client)
            ->with('success', __('Conversation :status.', ['status' => $status]));
    }

    public function downloadAttachment(Client $client, MessageThread $thread, MessageAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $this->authorizeThread($thread);
        if ($thread->client_id !== (int) $client->id) {
            abort(404);
        }
        if ($attachment->message->message_thread_id !== (int) $thread->id) {
            abort(404);
        }
        if (! $attachment->exists()) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->name,
            ['Content-Type' => $attachment->mime_type ?? 'application/octet-stream']
        );
    }
}
