<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\PlanLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ALOS-S1-10 — Client Document Center (portal). Client sees shared only; uploads are internal until office shares.
 */
class PortalDocumentController extends Controller
{
    private function getClient(\Illuminate\Contracts\Auth\Authenticatable $user): \App\Models\Client
    {
        $client = $user->client;
        if (! $client) {
            abort(404, __('Client not found.'));
        }
        return $client;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $client = $this->getClient($user);
        $tenant = $user->tenant;
        if ($tenant) {
            try {
                app(PlanLimitService::class)->ensureFeature($tenant, PlanLimitService::FEATURE_CLIENT_PORTAL);
            } catch (\RuntimeException $e) {
                abort(403, $e->getMessage());
            }
        }

        $documents = $client->documents()
            ->where('visibility', Document::VISIBILITY_SHARED)
            ->when($client->tenant_id, fn ($q) => $q->where('tenant_id', $client->tenant_id))
            ->with('uploader')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('portal.documents.index', [
            'client' => $client,
            'user' => $user,
            'documents' => $documents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $client = $this->getClient($user);
        $tenant = $client->tenant;
        if ($tenant) {
            $limitService = app(PlanLimitService::class);
            try {
                $limitService->checkDocumentLimit($tenant);
                $file = $request->file('file');
                $limitService->checkStorageLimit($tenant, $file ? $file->getSize() : 0);
            } catch (\Throwable $e) {
                return redirect()
                    ->route('portal.documents.index')
                    ->with('error', $e->getMessage());
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ]);

        $file = $request->file('file');
        $tmpPath = $this->getUploadTmpPath($request, 'file');
        $path = $this->storeUploadedFile($file, $client->id, $tmpPath);
        if (! $path) {
            return redirect()
                ->route('portal.documents.index')
                ->with('error', __('Failed to save the file. Please try again.'));
        }

        $client->documents()->create([
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'uploaded_by' => $user->id,
            'uploaded_by_type' => Document::UPLOADED_BY_CLIENT,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'visibility' => Document::VISIBILITY_INTERNAL, // client uploads = office only until shared
        ]);
        if ($tenant) {
            app(PlanLimitService::class)->invalidateUsageCache($tenant);
        }

        return redirect()
            ->route('portal.documents.index')
            ->with('success', __('Document uploaded. The office will review it.'));
    }

    public function download(Request $request, Document $document): StreamedResponse|RedirectResponse
    {
        $user = $request->user();
        $client = $this->getClient($user);
        if ($document->client_id !== (int) $client->id) {
            abort(404);
        }
        if ($client->tenant_id && (int) $document->tenant_id !== (int) $client->tenant_id) {
            abort(404);
        }
        if ($document->visibility !== Document::VISIBILITY_SHARED) {
            abort(404);
        }
        if (! $document->exists()) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        return Storage::disk($document->getStorageDisk())->download(
            $document->file_path,
            $document->file_name,
            ['Content-Type' => $document->mime_type ?? 'application/octet-stream']
        );
    }

    private function getUploadTmpPath(Request $request, string $key): ?string
    {
        $file = $request->file($key);
        if (! $file || ! $file->isValid()) {
            return null;
        }
        $raw = $_FILES[$key] ?? null;
        if ($raw && ! empty($raw['tmp_name']) && is_uploaded_file($raw['tmp_name']) && is_readable($raw['tmp_name'])) {
            return $raw['tmp_name'];
        }
        $path = $file->getRealPath();
        return ($path && is_readable($path)) ? $path : null;
    }

    private function storeUploadedFile($file, int $clientId, ?string $tmpPath): ?string
    {
        $dir = "documents/clients/{$clientId}";
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName()) ?: 'file';
        $path = $dir.'/'.uniqid().'_'.$safeName;
        try {
            if ($tmpPath) {
                $stored = Storage::disk('local')->put($path, fopen($tmpPath, 'r'));
            } else {
                $content = @$file->get();
                $stored = $content !== false && $content !== '' && Storage::disk('local')->put($path, $content);
            }
            return $stored ? $path : null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }
}
