@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseSession::STATUSES;
  $prevMonth = (clone $monthDate)->modify('-1 month')->format('Y-m');
  $nextMonth = (clone $monthDate)->modify('+1 month')->format('Y-m');
  $sessionsByDate = $sessions->groupBy(fn ($s) => $s->session_date->format('Y-m-d'));
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Calendar') . ' — ' . $case->case_number . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('admin.core.cases.show', $case) }}">{{ $case->case_number }}</a></li>
          <li class="breadcrumb-item"><a href="{{ route('admin.core.cases.sessions.index', $case) }}">{{ __('Sessions') }}</a></li>
          <li class="breadcrumb-item active">{{ __('Calendar') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ __('Court Hearings Calendar') }}</h4>
      <p class="text-muted small mb-0">{{ $monthDate->format('F Y') }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.core.cases.sessions.calendar', [$case, 'month' => $prevMonth]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="icon-base ti tabler-chevron-left"></i> {{ __('Previous') }}
      </a>
      <a href="{{ route('admin.core.cases.sessions.calendar', [$case, 'month' => $nextMonth]) }}" class="btn btn-outline-secondary btn-sm">
        {{ __('Next') }} <i class="icon-base ti tabler-chevron-right"></i>
      </a>
      <a href="{{ route('admin.core.cases.sessions.index', $case) }}" class="btn btn-outline-primary btn-sm">{{ __('List view') }}</a>
      @can('cases.manage')
      <a href="{{ route('admin.core.cases.sessions.create', $case) }}" class="btn btn-primary btn-sm">{{ __('Add Session') }}</a>
      @endcan
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      @if($sessions->isEmpty())
        <div class="text-center text-muted py-5">
          <i class="icon-base ti tabler-calendar-event icon-32px d-block mb-2 opacity-50"></i>
          {{ __('No sessions in this month.') }}
          @can('cases.manage')
            <a href="{{ route('admin.core.cases.sessions.create', $case) }}">{{ __('Add Session') }}</a>
          @endcan
        </div>
      @else
        @foreach($sessionsByDate as $date => $daySessions)
          <div class="border-bottom pb-3 mb-3">
            <h6 class="text-primary mb-2">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h6>
            <ul class="list-unstyled mb-0">
              @foreach($daySessions as $s)
                @php $sc = match($s->status) { 'scheduled' => 'primary', 'completed' => 'success', 'cancelled' => 'danger', 'postponed' => 'warning', default => 'secondary' }; @endphp
                <li class="d-flex flex-wrap align-items-center gap-2 py-2 border-bottom border-light">
                  <span class="badge bg-label-{{ $sc }}">{{ __($statusLabels[$s->status] ?? $s->status) }}</span>
                  @if($s->session_time)
                    <span class="fw-medium">{{ substr($s->session_time, 0, 5) }}</span>
                  @endif
                  <span>{{ $s->court_name ?? __('Session') }}</span>
                  @if($s->location)<span class="text-muted small">· {{ $s->location }}</span>@endif
                  @if($s->assignedUser)<span class="text-muted small">· {{ $s->assignedUser->name }}</span>@endif
                  @can('cases.manage')
                  <a href="{{ route('admin.core.cases.sessions.edit', [$case, $s]) }}" class="btn btn-icon btn-sm btn-text-primary rounded ms-auto"><i class="icon-base ti tabler-pencil"></i></a>
                  @endcan
                </li>
              @endforeach
            </ul>
          </div>
        @endforeach
      @endif
    </div>
  </div>
</div>
@endsection
