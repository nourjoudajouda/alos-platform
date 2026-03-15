@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $badgeLabels = [
    'today' => __('Today'),
    'tomorrow' => __('Tomorrow'),
    'upcoming' => __('Upcoming'),
  ];
@endphp

@extends('portal::layouts.portal')

@section('title', __('Sessions') . ' — ' . __('Client Portal'))

@section('content')
  <div class="mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Sessions') }}</li>
      </ol>
    </nav>
    <h4 class="fw-bold mb-1">{{ __('Upcoming Sessions') }}</h4>
    <p class="text-muted small mb-0">{{ __('Court sessions and hearings related to your cases.') }}</p>
  </div>

  <div class="card">
      <div class="card-body p-0">
        @forelse($sessions as $s)
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-grow-1 min-w-0">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-medium">{{ $s['session_date_formatted'] ?? '—' }}</span>
                @if (!empty($s['session_time']))
                  <span class="text-muted">{{ $s['session_time'] }}</span>
                @endif
                @isset($badgeLabels[$s['badge'] ?? ''])
                  <span class="badge bg-{{ $s['badge'] === 'today' ? 'primary' : ($s['badge'] === 'tomorrow' ? 'info' : 'secondary') }}">
                    {{ $badgeLabels[$s['badge']] }}
                  </span>
                @endisset
              </div>
              @if (!empty($s['court_name']))
                <div class="small text-muted mt-1">{{ $s['court_name'] }}</div>
              @endif
              <div class="small mt-1">
                <span class="text-body">{{ __('Case') }}: {{ $s['case_number'] ?? '—' }}</span>
                @if (!empty($s['case_title']) && $s['case_title'] !== ($s['case_number'] ?? ''))
                  <span class="text-muted"> · {{ $s['case_title'] }}</span>
                @endif
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-5 px-4">
            <i class="icon-base ti tabler-calendar-off icon-32px text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">{{ __('No upcoming sessions.') }}</p>
            <p class="small text-muted mt-1">{{ __('When new court sessions are scheduled for your cases, they will appear here.') }}</p>
          </div>
        @endforelse
      </div>
      @if ($sessions->hasPages())
        <div class="card-footer d-flex justify-content-center">
          {{ $sessions->links() }}
        </div>
      @endif
    </div>
@endsection
