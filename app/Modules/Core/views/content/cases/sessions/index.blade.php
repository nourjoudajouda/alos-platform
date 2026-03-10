@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseSession::STATUSES;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Sessions') . ' — ' . $case->case_number . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('admin.core.cases.show', $case) }}">{{ $case->case_number }}</a></li>
          <li class="breadcrumb-item active">{{ __('Sessions') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ __('Court Hearings') }}</h4>
      <p class="text-muted small mb-0">{{ __('Sessions and hearings for case :number', ['number' => $case->case_number]) }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.core.cases.sessions.calendar', $case) }}" class="btn btn-outline-primary btn-sm">
        <i class="icon-base ti tabler-calendar {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Calendar') }}
      </a>
      @can('cases.manage')
      <a href="{{ route('admin.core.cases.sessions.create', $case) }}" class="btn btn-primary btn-sm">
        <i class="icon-base ti tabler-plus {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Add Session') }}
      </a>
      @endcan
      <a href="{{ route('admin.core.cases.show', [$case, 'tab' => 'sessions']) }}" class="btn btn-outline-secondary btn-sm">{{ __('Case profile') }}</a>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('Sessions list') }}</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Time') }}</th>
            <th>{{ __('Court') }}</th>
            <th>{{ __('Location') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Assigned to') }}</th>
            @can('cases.manage')<th class="text-end" style="min-width: 7rem;">{{ __('Actions') }}</th>@endcan
          </tr>
        </thead>
        <tbody>
          @forelse($sessions as $s)
            @php
              $statusClass = match($s->status) {
                'scheduled' => 'primary',
                'completed' => 'success',
                'cancelled' => 'danger',
                'postponed' => 'warning',
                default => 'secondary',
              };
            @endphp
            <tr>
              <td class="text-nowrap">{{ $s->session_date->format('Y-m-d') }}</td>
              <td class="text-nowrap">{{ $s->session_time ? substr($s->session_time, 0, 5) : '—' }}</td>
              <td>{{ $s->court_name ?? '—' }}</td>
              <td>{{ $s->location ?? '—' }}</td>
              <td><span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$s->status] ?? $s->status) }}</span></td>
              <td>{{ $s->assignedUser?->name ?? '—' }}</td>
              @can('cases.manage')
              <td class="text-end text-nowrap">
                <a href="{{ route('admin.core.cases.sessions.edit', [$case, $s]) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
                  <i class="icon-base ti tabler-pencil"></i>
                </a>
                <form action="{{ route('admin.core.cases.sessions.destroy', [$case, $s]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this session?') }}');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}">
                    <i class="icon-base ti tabler-trash"></i>
                  </button>
                </form>
              </td>
              @endcan
            </tr>
          @empty
            <tr>
              <td colspan="{{ auth()->user()->can('cases.manage') ? 7 : 6 }}" class="text-center text-muted py-5">
                <i class="icon-base ti tabler-calendar-event icon-32px d-block mb-2 opacity-50"></i>
                {{ __('No sessions yet.') }}
                @can('cases.manage')
                  <a href="{{ route('admin.core.cases.sessions.create', $case) }}">{{ __('Add Session') }}</a>
                @endcan
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($sessions->hasPages())
      <div class="card-footer">{{ $sessions->links() }}</div>
    @endif
  </div>
</div>
@endsection
