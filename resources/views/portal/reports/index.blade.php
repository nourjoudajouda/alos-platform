@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp

@extends('portal::layouts.portal')

@section('title', __('Reports') . ' — ' . __('Client Portal'))

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
    <div class="mb-4">
      <h4 class="fw-bold mb-1">{{ __('Reports') }}</h4>
      <p class="text-muted small mb-0">{{ __('Reports shared with you by the office: case status, activity summary, and new documents.') }}</p>
    </div>

    <div class="card">
      <div class="card-body p-0">
        @forelse($reports as $r)
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-shrink-0">
              <span class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-primary">
                  <i class="icon-base ti tabler-file-report"></i>
                </span>
              </span>
            </div>
            <div class="flex-grow-1 min-w-0">
              <span class="badge bg-label-primary me-2">{{ $reportTypes[$r->report_type] ?? $r->report_type }}</span>
              <strong class="d-block text-truncate">{{ Str::limit($r->title, 60) }}</strong>
              <small class="text-muted">{{ __('Generated at') }} {{ $r->generated_at?->format('Y-m-d H:i') }}</small>
            </div>
            <div class="flex-shrink-0">
              <a href="{{ route('portal.reports.show', $r) }}" class="btn btn-sm btn-primary">
                <i class="icon-base ti tabler-eye {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
                {{ __('View') }}
              </a>
            </div>
          </div>
        @empty
          <div class="text-center py-5 text-muted p-4">
            <i class="icon-base ti tabler-file-report icon-32px d-block mb-3 opacity-50"></i>
            <p class="mb-0">{{ __('No reports yet. The office will share reports with you according to your report settings.') }}</p>
          </div>
        @endforelse
      </div>
      @if($reports->hasPages())
        <div class="card-footer d-flex justify-content-center">
          {{ $reports->links() }}
        </div>
      @endif
    </div>
  </div>
@endsection
