<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\MessageThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ALOS-S1-09 — Secure Messaging (Client Portal). Only for the authenticated client's threads.
 */
class PortalMessageController extends Controller
{
    private function getClient(\Illuminate\Contracts\Auth\Authenticatable $user): \App\Models\Client
    {
        $client = $user->client;
        if (! $client) {
            abort(404, __('Client not found.'));
        }
        return $client;
    }

    private function ensureCanMessage(Request $request): void
    {
        $user = $request->user();
        if (! $user->isClientPortalUser()) {
            abort(403, __('Access denied.'));
        }
        $permission = $user->portal_permission ?? \App\Models\User::PORTAL_PERMISSION_VIEW_ONLY;
        if ($permission === \App\Models\User::PORTAL_PERMISSION_VIEW_ONLY) {
            abort(403, __('Your account does not have messaging permission.'));
        }
    }

    private function ensureCanUpload(Request $request): void
    {
        $this->ensureCanMessage($request);
        $user = $request->user();
        if ($user->portal_permission !== \App\Models\User::PORTAL_PERMISSION_MESSAGING_UPLOAD) {
            abort(403, __('Your account does not have permission to upload files.'));
        }
    }

    private function authorizeThread(MessageThread $thread, int $clientId): void
    {
        if ((int) $thread->client_id !== $clientId) {
            abort(404);
        }
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $this->getClient($user);

        $query = $client->messageThreads()->with(['messages' => fn ($q) => $q->latest()->limit(1)->with('user')]);
        if (! $request->boolean('archived')) {
            $query->whereNull('archived_at');
        } else {
            $query->whereNotNull('archived_at');
        }
        $threads = $query->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $canSend = in_array($user->portal_permission, [
            \App\Models\User::PORTAL_PERMISSION_MESSAGING,
            \App\Models\User::PORTAL_PERMISSION_MESSAGING_UPLOAD,
        ], true);

        return view('portal.messages.index', [
            'client' => $client,
            'user' => $user,
            'threads' => $threads,
            'showArchived' => $request->boolean('archived'),
            'canSend' => $canSend,
        ]);
    }

    public function show(Request $request, MessageThread $thread): View
    {
        $user = $request->user();
        $client = $this->getClient($user);
        $this->authorizeThread($thread, (int) $client->id);

        $thread->load(['messages.user', 'messages.attachments']);

        $canSend = in_array($user->portal_permission, [
            \App\Models\User::PORTAL_PERMISSION_MESSAGING,
            \App\Models\User::PORTAL_PERMISSION_MESSAGING_UPLOAD,
        ], true);
        $canUpload = $user->portal_permission === \App\Models\User::PORTAL_PERMISSION_MESSAGING_UPLOAD;

        return view('portal.messages.show', [
            'client' => $client,
            'user' => $user,
            'thread' => $thread,
            'canSend' => $canSend,
            'canUpload' => $canUpload,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCanMessage($request);
        $user = $request->user();
        $client = $this->getClient($user);

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
        ]);

        $thread = $client->messageThreads()->create([
            'subject' => $validated['subject'],
        ]);

        return redirect()
            ->route('portal.messages.show', $thread)
            ->with('success', __('Conversation started.'));
    }

    public function storeMessage(Request $request, MessageThread $thread): RedirectResponse
    {
        $this->ensureCanMessage($request);
        $user = $request->user();
        $client = $this->getClient($user);
        $this->authorizeThread($thread, (int) $client->id);

        if ($thread->archived_at) {
            return redirect()
                ->route('portal.messages.show', $thread)
                ->with('error', __('This conversation is archived.'));
        }

        $canUpload = $user->portal_permission === \App\Models\User::PORTAL_PERMISSION_MESSAGING_UPLOAD;
        $rules = [
            'body' => ['required', 'string', 'max:10000'],
        ];
        if ($canUpload) {
            $rules['attachments.*'] = ['nullable', 'file', 'max:10240'];
        }

        $validated = $request->validate($rules);

        $message = $thread->messages()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);
        $message->load('user');
        \App\Notifications\InApp\NewMessageNotification::send($thread, $message);

        if ($canUpload && $request->hasFile('attachments')) {
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
            ->route('portal.messages.show', $thread)
            ->with('success', __('Message sent.'));
    }

    public function downloadAttachment(Request $request, MessageThread $thread, MessageAttachment $attachment): StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        $client = $this->getClient($user);
        $this->authorizeThread($thread, (int) $client->id);
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
