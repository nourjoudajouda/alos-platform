<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ALOS-S1-14 — Client Portal Consultations. Client sees only shared consultations (summary + shared docs).
 */
class PortalConsultationController extends Controller
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

        $consultations = $client->consultations()
            ->where('is_shared_with_client', true)
            ->with('responsibleUser')
            ->orderByDesc('consultation_date')
            ->paginate(15)
            ->withQueryString();

        return view('portal.consultations.index', [
            'client' => $client,
            'consultations' => $consultations,
        ]);
    }

    public function show(Request $request, Consultation $consultation): View
    {
        $user = $request->user();
        $client = $this->getClient($user);

        if ($consultation->client_id !== (int) $client->id) {
            abort(404);
        }
        if (! $consultation->is_shared_with_client) {
            abort(404);
        }

        // Only shared documents
        $sharedDocuments = $consultation->documents()
            ->where('visibility', Document::VISIBILITY_SHARED)
            ->orderByDesc('created_at')
            ->get();

        // Linked message threads (client can access via messages if they have messaging permission)
        $linkedThreads = $consultation->messageThreads()->orderByDesc('created_at')->get();

        return view('portal.consultations.show', [
            'consultation' => $consultation,
            'client' => $client,
            'sharedDocuments' => $sharedDocuments,
            'linkedThreads' => $linkedThreads,
        ]);
    }

    public function downloadDocument(Request $request, Consultation $consultation, int $documentId): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $client = $this->getClient($user);

        if ($consultation->client_id !== (int) $client->id || ! $consultation->is_shared_with_client) {
            abort(404);
        }

        $document = $consultation->documents()
            ->where('id', (int) $documentId)
            ->where('visibility', Document::VISIBILITY_SHARED)
            ->firstOrFail();

        if (! $document->exists()) {
            return redirect()->back()->with('error', __('File not found.'));
        }

        return Storage::disk($document->getStorageDisk())->download(
            $document->file_path,
            $document->file_name,
            ['Content-Type' => $document->mime_type ?? 'application/octet-stream']
        );
    }
}
