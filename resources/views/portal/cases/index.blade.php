@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseModel::STATUSES;
@endphp

@extends('portal::layouts.portal')

@section('title', __('My Cases') . ' — ' . __('Client Portal'))

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
    <div class="mb-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">{{ __('Dashboard') }}</a></li>
          <li class="breadcrumb-item active">{{ __('My Cases') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ __('My Cases') }}</h4>
      <p class="text-muted small mb-0">{{ __('Your cases with the office. For detailed consultations and documents, use the Documents and Consultations sections.') }}</p>
    </div>

    <div class="card">
      <div class="card-body p-0">
        @forelse($cases as $case)
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-grow-1 min-w-0">
              <a href="{{ route('portal.cases.show', $case) }}" class="fw-medium d-block text-body">{{ $case->case_number ?? '—' }}</a>
              <div class="small text-muted">
                {{ $case->case_type ?? '—' }}
                <span class="badge bg-label-{{ $case->status === 'open' ? 'success' : ($case->status === 'closed' ? 'secondary' : 'warning') }} ms-1">
                  {{ __($statusLabels[$case->status ?? ''] ?? ucfirst((string) $case->status)) }}
                </span>
                @if ($case->responsibleLawyer)
                  · {{ $case->responsibleLawyer->name }}
                @endif
                · {{ __('Last updated') }} {{ $case->updated_at?->diffForHumans() }}
              </div>
            </div>
            <a href="{{ route('portal.cases.show', $case) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
          </div>
        @empty
          <div class="text-center py-5 px-3">
            <i class="icon-base ti tabler-briefcase icon-32px text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">{{ __('No cases yet.') }}</p>
            <p class="small text-muted mt-1">{{ __('When the office opens a case for you, it will appear here.') }}</p>
          </div>
        @endforelse
      </div>
      @if ($cases->hasPages())
        <div class="card-footer d-flex justify-content-center">
          {{ $cases->links() }}
        </div>
      @endif
    </div>
  </div>
@endsection
