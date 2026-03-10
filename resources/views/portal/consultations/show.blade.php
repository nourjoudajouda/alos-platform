@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\Consultation::STATUSES;
  $statusClass = match($consultation->status) {
    'open' => 'primary',
    'completed' => 'success',
    'archived' => 'secondary',
    default => 'secondary',
  };
@endphp

@extends('portal::layouts.portal')

@section('title', $consultation->title . ' — ' . __('Client Portal'))

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
    <div class="mb-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('portal.consultations.index') }}">{{ __('Consultations') }}</a></li>
          <li class="breadcrumb-item active">{{ $consultation->title }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ $consultation->title }}</h4>
      <p class="text-muted small mb-0">
        {{ $consultation->consultation_date?->format('Y-m-d') ?? '—' }}
        · {{ $consultation->responsibleUser?->name ?? '—' }}
        · <span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$consultation->status] ?? $consultation->status) }}</span>
      </p>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Summary (client sees only this, not internal_notes) --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Summary') }}</h5>
      </div>
      <div class="card-body">
        @if ($consultation->summary)
          <div class="mb-0">{!! nl2br(e($consultation->summary)) !!}</div>
        @else
          <p class="text-muted mb-0">{{ __('No summary available.') }}</p>
        @endif
      </div>
    </div>

    {{-- Shared documents only --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Shared documents') }}</h5>
      </div>
      <div class="card-body p-0">
        @forelse($sharedDocuments as $doc)
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-shrink-0">
              <span class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-secondary">
                  <i class="icon-base ti tabler-file"></i>
                </span>
              </span>
            </div>
            <div class="flex-grow-1 min-w-0">
              <span class="fw-medium d-block">{{ $doc->name }}</span>
              <div class="small text-muted">
                {{ $doc->file_name }}
                @if ($doc->file_size)
                  · {{ number_format($doc->file_size / 1024, 1) }} KB
                @endif
              </div>
            </div>
            <a href="{{ route('portal.consultations.download-document', ['consultation' => $consultation, 'documentId' => $doc->id]) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
              <i class="icon-base ti tabler-download {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Download') }}
            </a>
          </div>
        @empty
          <div class="text-center py-4 text-muted">
            <i class="icon-base ti tabler-folder-off icon-32px d-block mb-2 opacity-50"></i>
            <p class="mb-0">{{ __('No documents shared for this consultation.') }}</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Linked message threads (client can open if they have messaging permission) --}}
    @if ($linkedThreads->isNotEmpty())
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Related conversations') }}</h5>
      </div>
      <div class="card-body p-0">
        @foreach ($linkedThreads as $thread)
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-shrink-0">
              <span class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="icon-base ti tabler-message"></i>
                </span>
              </span>
            </div>
            <div class="flex-grow-1 min-w-0">
              <span class="fw-medium d-block">{{ $thread->subject }}</span>
              <div class="small text-muted">{{ $thread->created_at?->format('Y-m-d H:i') }}</div>
            </div>
            <a href="{{ route('portal.messages.show', $thread) }}" class="btn btn-sm btn-outline-primary">
              <i class="icon-base ti tabler-eye {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('View conversation') }}
            </a>
          </div>
        @endforeach
      </div>
    </div>
    @endif

    <a href="{{ route('portal.consultations.index') }}" class="btn btn-outline-secondary">
      <i class="icon-base ti tabler-arrow-left {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
      {{ __('Back to consultations') }}
    </a>
  </div>
@endsection
