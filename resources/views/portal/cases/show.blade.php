@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseModel::STATUSES;
  $statusClass = match($case->status ?? '') {
    'open' => 'success',
    'closed' => 'secondary',
    'pending' => 'warning',
    default => 'secondary',
  };
  $sessionStatusLabels = \App\Models\CaseSession::STATUSES;
@endphp

@extends('portal::layouts.portal')

@section('title', ($case->case_number ?? __('Case')) . ' — ' . __('Client Portal'))

@section('content')
  <div class="mb-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">{{ __('Dashboard') }}</a></li>
          <li class="breadcrumb-item"><a href="{{ route('portal.cases.index') }}">{{ __('My Cases') }}</a></li>
          <li class="breadcrumb-item active">{{ $case->case_number ?? __('Case') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ $case->case_number ?? '—' }}</h4>
      <p class="text-muted small mb-0">
        {{ $case->case_type ?? '—' }}
        · <span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$case->status ?? ''] ?? ucfirst((string) $case->status)) }}</span>
        @if ($case->updated_at)
          · {{ __('Last updated') }} {{ $case->updated_at->diffForHumans() }}
        @endif
      </p>
    </div>

    {{-- Assigned lawyer --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Assigned lawyer') }}</h5>
      </div>
      <div class="card-body">
        @if ($case->responsibleLawyer)
          <span class="fw-medium">{{ $case->responsibleLawyer->name }}</span>
        @else
          <p class="text-muted mb-0">{{ __('Not assigned yet.') }}</p>
        @endif
      </div>
    </div>

    {{-- Upcoming sessions (safe fields only: date, time, court, case reference) --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Sessions') }}</h5>
      </div>
      <div class="card-body p-0">
        @forelse($sessions as $session)
          <div class="border-bottom border-secondary p-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="fw-medium">{{ $session->session_date?->format('Y-m-d') ?? '—' }}</span>
              <span class="text-muted">{{ $session->session_time ?? '—' }}</span>
              <span class="badge bg-label-secondary">{{ __($sessionStatusLabels[$session->status ?? ''] ?? $session->status) }}</span>
            </div>
            <div class="small text-muted mt-1">
              {{ __('Court') }}: {{ $session->court_name ?? '—' }}
              · {{ __('Case reference') }}: {{ $case->case_number ?? '—' }}
            </div>
          </div>
        @empty
          <div class="text-center py-4 px-3">
            <p class="text-muted mb-0">{{ __('No sessions recorded.') }}</p>
          </div>
        @endforelse
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
            <a href="{{ route('portal.documents.download', $doc) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
              <i class="icon-base ti tabler-download {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Download') }}
            </a>
          </div>
        @empty
          <div class="text-center py-4 px-3">
            <p class="text-muted mb-0">{{ __('No shared documents for this case.') }}</p>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Related conversations --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Related conversations') }}</h5>
      </div>
      <div class="card-body p-0">
        @forelse($threads as $thread)
          <div class="border-bottom border-secondary p-3">
            <a href="{{ route('portal.messages.show', $thread) }}" class="fw-medium d-block">{{ $thread->subject ?? __('Conversation') }}</a>
            <div class="small text-muted">{{ $thread->created_at?->diffForHumans() }}</div>
          </div>
        @empty
          <div class="text-center py-4 px-3">
            <p class="text-muted mb-0">{{ __('No conversations linked to this case.') }}</p>
          </div>
        @endforelse
      </div>
    </div>

  <div class="mb-2">
    <a href="{{ route('portal.cases.index') }}" class="btn btn-outline-secondary"><i class="icon-base ti tabler-arrow-left {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>{{ __('Back to My Cases') }}</a>
  </div>
@endsection
