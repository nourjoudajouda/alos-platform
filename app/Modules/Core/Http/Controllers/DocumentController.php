<?php

namespace App\Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMajorUpdateReportJob;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Document;
use App\Services\AuditLogService;
use App\Services\PlanLimitService;
use Illuminate\Support\Facades\App;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ALOS-S1-10 — Client Document Center (office). Upload, list, visibility, secure download.
 */
class DocumentController extends Controller
{
    protected function isCompanyContext(): bool
    {
        return str_starts_with(request()->route()->getName() ?? '', 'company.');
    }

    protected function clientRoutePrefix(): string
    {
        return $this->isCompanyContext() ? 'company.clients' : 'admin.core.clients';
    }

    private function authorizeClient(Client $client): void
    {
        $user = auth()->user();
        if ($user->isClientPortalUser()) {
            App::make(AuditLogService::class)->recordCompliance('access_document', __('Client portal user attempted office document access.'), 'client', $client->id, $client->tenant_id);
            abort(403, __('Access denied. Use the client portal for documents.'));
        }
        if ($client->tenant_id && $user->tenant_id && (int) $client->tenant_id !== (int) $user->tenant_id) {
            App::make(AuditLogService::class)->recordCompliance('access_document', __('Attempted document access for client in another tenant.'), 'client', $client->id, $client->tenant_id);
            abort(403, __('Access denied.'));
        }
        if (! $client->teamAccess()->where('user_id', $user->id)->exists()) {
            App::make(AuditLogService::class)->recordCompliance('access_document', __('Attempted document access for client outside team.'), 'client', $client->id, $client->tenant_id);
            abort(403, __('You do not have access to this client.'));
        }
    }

    private function authorizeDocument(Document $document): void
    {
        $this->authorizeClient($document->client);
    }

    public function index(Request $request, Client $client): View
    {
        $this->authorizeClient($client);

        $query = $client->documents()->with(['uploader', 'case', 'consultation']);
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->get('visibility'));
        }
        if ($request->filled('case_id')) {
            $query->where('case_id', $request->get('case_id'));
        }
        if ($request->filled('consultation_id')) {
            $query->where('consultation_id', $request->get('consultation_id'));
        }
        $documents = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('core::content.clients.documents.index', [
            'client' => $client,
            'documents' => $documents,
            'filterVisibility' => $request->get('visibility', ''),
            'filterCaseId' => $request->get('case_id', ''),
            'filterConsultationId' => $request->get('consultation_id', ''),
            'consultations' => $client->consultations()->orderByDesc('consultation_date')->get(),
            'clientCases' => $client->cases()->orderByDesc('updated_at')->get(),
            'clientRoutePrefix' => $this->clientRoutePrefix(),
        ]);
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);

        $tenant = $client->tenant;
        if ($tenant) {
            $limitService = app(PlanLimitService::class);
            try {
                $limitService->checkDocumentLimit($tenant);
                $file = $request->file('file');
                $limitService->checkStorageLimit($tenant, $file ? $file->getSize() : 0);
            } catch (\Throwable $e) {
                return redirect()
                    ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'documents'])
                    ->with('error', $e->getMessage());
            }
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', Rule::in([Document::VISIBILITY_INTERNAL, Document::VISIBILITY_SHARED])],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'], // 20MB
            'consultation_id' => ['nullable', 'integer', 'exists:consultations,id'],
            'case_id' => ['nullable', 'integer', 'exists:cases,id'],
        ]);

        $file = $request->file('file');
        $tmpPath = $this->getUploadTmpPath($request, 'file');
        $path = $this->storeUploadedFile($file, $client->id, $tmpPath);
        if (! $path) {
            return redirect()
                ->route($this->clientRoutePrefix() . '.show', [$client, 'tab' => 'documents'])
                ->with('error', __('Failed to save the file. Please try again.'));
        }

        $data = [
            'tenant_id' => $client->tenant_id,
            'client_id' => $client->id,
            'uploaded_by' => auth()->id(),
            'uploaded_by_type' => Document::UPLOADED_BY_INTERNAL,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'visibility' => $validated['visibility'],
        ];
        if (! empty($validated['consultation_id'])) {
            $consultation = \App\Models\Consultation::where('client_id', $client->id)->find($validated['consultation_id']);
            if ($consultation) {
                $data['consultation_id'] = $consultation->id;
            }
        }
        if (! empty($validated['case_id'])) {
            $case = \App\Models\CaseModel::where('client_id', $client->id)->find($validated['case_id']);
            if ($case) {
                $data['case_id'] = $case->id;
            }
        }
        $doc = $client->documents()->create($data);
        if ($tenant) {
            app(PlanLimitService::class)->invalidateUsageCache($tenant);
        }
        $actionType = $data['visibility'] === Document::VISIBILITY_SHARED ? AuditLog::ACTION_SHARE_DOCUMENT : AuditLog::ACTION_UPLOAD_DOCUMENT;
        App::make(AuditLogService::class)->recordAudit($actionType, AuditLog::ENTITY_DOCUMENT, $doc->id, [], ['visibility' => $data['visibility'], 'name' => $doc->name], $client->tenant_id);
        if ($data['visibility'] === Document::VISIBILITY_SHARED) {
            ProcessMajorUpdateReportJob::dispatch($client->id, 'document_shared');
        }

        $redirect = route($this->clientRoutePrefix() . '.documents.index', $client);
        $params = [];
        if (! empty($validated['consultation_id'])) {
            $params['consultation_id'] = $validated['consultation_id'];
        }
        if (! empty($validated['case_id'])) {
            $params['case_id'] = $validated['case_id'];
        }

        return redirect($params ? $redirect . '?' . http_build_query($params) : $redirect)
            ->with('success', __('Document uploaded successfully.'));
    }

    public function updateVisibility(Request $request, Client $client, Document $document): RedirectResponse
    {
        $this->authorizeDocument($document);
        if ($document->client_id !== (int) $client->id) {
            abort(404);
        }

        $validated = $request->validate([
            'visibility' => ['required', Rule::in([Document::VISIBILITY_INTERNAL, Document::VISIBILITY_SHARED])],
        ]);

        $oldValues = $document->only(['visibility']);
        $document->update(['visibility' => $validated['visibility']]);
        $newValues = $document->only(['visibility']);
        App::make(AuditLogService::class)->recordAuditWithChanges(AuditLog::ACTION_SHARE_DOCUMENT, AuditLog::ENTITY_DOCUMENT, $document->id, $oldValues, $newValues, $document->tenant_id);
        if ($validated['visibility'] === Document::VISIBILITY_SHARED) {
            ProcessMajorUpdateReportJob::dispatch($document->client_id, 'document_shared');
            \App\Notifications\InApp\DocumentSharedNotification::send($document);
        }

        return redirect()
            ->route($this->clientRoutePrefix() . '.documents.index', $client)
            ->with('success', __('Document visibility updated.'));
    }

    public function download(Client $client, Document $document): StreamedResponse|RedirectResponse
    {
        $this->authorizeDocument($document);
        if ($document->client_id !== (int) $client->id) {
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
