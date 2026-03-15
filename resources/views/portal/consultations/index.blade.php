@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\Consultation::STATUSES;
@endphp

@extends('portal::layouts.portal')

@section('title', __('Consultations') . ' — ' . __('Client Portal'))

@section('content')
  <div class="mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Consultations') }}</li>
      </ol>
    </nav>
    <h4 class="fw-bold mb-1">{{ __('My Consultations') }}</h4>
    <p class="text-muted small mb-0">{{ __('Consultations shared with you by the office.') }}</p>
  </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Consultations') }}</h5>
      </div>
      <div class="card-body p-0">
        @forelse($consultations as $consultation)
          @php
            $statusClass = match($consultation->status) {
              'open' => 'primary',
              'completed' => 'success',
              'archived' => 'secondary',
              default => 'secondary',
            };
          @endphp
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-shrink-0">
              <span class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="icon-base ti tabler-calendar-event"></i>
                </span>
              </span>
            </div>
            <div class="flex-grow-1 min-w-0">
              <a href="{{ route('portal.consultations.show', $consultation) }}" class="fw-medium d-block">{{ $consultation->title }}</a>
              <div class="small text-muted">
                {{ $consultation->consultation_date?->format('Y-m-d') ?? '—' }}
                · {{ $consultation->responsibleUser?->name ?? '—' }}
                · <span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$consultation->status] ?? $consultation->status) }}</span>
              </div>
              @if ($consultation->summary)
                <p class="small text-muted mb-0 mt-1">{{ Str::limit($consultation->summary, 100) }}</p>
              @endif
            </div>
            <a href="{{ route('portal.consultations.show', $consultation) }}" class="btn btn-sm btn-outline-primary">
              <i class="icon-base ti tabler-eye {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('View') }}
            </a>
          </div>
        @empty
          <div class="text-center py-5 px-4">
            <i class="icon-base ti tabler-calendar-off icon-32px text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">{{ __('No consultations shared with you yet.') }}</p>
            <p class="small text-muted mt-1">{{ __('When the office shares a consultation with you, it will appear here.') }}</p>
          </div>
        @endforelse
      </div>
      @if ($consultations->hasPages())
        <div class="card-footer d-flex justify-content-center">
          {{ $consultations->links() }}
        </div>
      @endif
    </div>
@endsection
