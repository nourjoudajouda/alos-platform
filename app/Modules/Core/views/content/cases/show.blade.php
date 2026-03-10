@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $activeTab = request('tab', 'overview');
  $tabs = ['overview', 'sessions', 'documents', 'messages', 'activity'];
  if (!in_array($activeTab, $tabs, true)) $activeTab = 'overview';
  $statusLabels = \App\Models\CaseModel::STATUSES;
  $statusClass = match($case->status) {
    'open' => 'primary',
    'pending' => 'warning',
    'closed' => 'secondary',
    default => 'secondary',
  };
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $case->case_number . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  {{-- Case profile header --}}
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-xl">
            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-briefcase"></i></span>
          </span>
          <div>
            <h4 class="mb-1">{{ $case->case_number }}</h4>
            <p class="text-muted mb-1 small">{{ $case->case_type ?? '—' }} @if($case->court_name) · {{ $case->court_name }} @endif</p>
            <a href="{{ route('admin.core.clients.show', $case->client) }}" class="text-primary small">{{ $case->client->name }}</a>
            <span class="badge bg-label-{{ $statusClass }} ms-2">{{ __($statusLabels[$case->status] ?? $case->status) }}</span>
            <span class="text-muted small ms-2">{{ __('Updated') }} {{ $case->updated_at?->format('Y-m-d') }}</span>
          </div>
        </div>
        <div class="d-flex gap-2">
          @can('cases.manage')
          <a href="{{ route('admin.core.cases.edit', $case) }}" class="btn btn-warning btn-sm">
            <i class="icon-base ti tabler-pencil {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Edit') }}
          </a>
          <form action="{{ route('admin.core.cases.destroy', $case) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this case?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Delete') }}</button>
          </form>
          @endcan
          <a href="{{ route('admin.core.clients.show', [$case->client, 'tab' => 'cases']) }}" class="btn btn-outline-secondary btn-sm">{{ __('Client profile') }}</a>
          <a href="{{ route('admin.core.cases.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Case profile tabs: Overview | Sessions | Documents | Messages | Activity Log --}}
  <ul class="nav nav-tabs nav-fill mb-3" id="caseProfileTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" href="{{ route('admin.core.cases.show', ['case' => $case, 'tab' => 'overview']) }}" role="tab">{{ __('Overview') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'sessions' ? 'active' : '' }}" href="{{ route('admin.core.cases.show', ['case' => $case, 'tab' => 'sessions']) }}" role="tab">{{ __('Sessions') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}" href="{{ route('admin.core.cases.show', ['case' => $case, 'tab' => 'documents']) }}" role="tab">{{ __('Documents') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'messages' ? 'active' : '' }}" href="{{ route('admin.core.cases.show', ['case' => $case, 'tab' => 'messages']) }}" role="tab">{{ __('Messages') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'activity' ? 'active' : '' }}" href="{{ route('admin.core.cases.show', ['case' => $case, 'tab' => 'activity']) }}" role="tab">{{ __('Activity Log') }}</a>
    </li>
  </ul>

  <div class="tab-content" id="caseProfileTabContent">
    {{-- Overview --}}
    <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Overview') }}</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Case number') }}</dt>
            <dd class="col-sm-9">{{ $case->case_number }}</dd>
            <dt class="col-sm-3">{{ __('Client') }}</dt>
            <dd class="col-sm-9"><a href="{{ route('admin.core.clients.show', $case->client) }}">{{ $case->client->name }}</a></dd>
            <dt class="col-sm-3">{{ __('Case type') }}</dt>
            <dd class="col-sm-9">{{ $case->case_type ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Court name') }}</dt>
            <dd class="col-sm-9">{{ $case->court_name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Responsible lawyer') }}</dt>
            <dd class="col-sm-9">{{ $case->responsibleLawyer?->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9"><span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$case->status] ?? $case->status) }}</span></dd>
            <dt class="col-sm-3">{{ __('Description') }}</dt>
            <dd class="col-sm-9">{{ $case->description ? nl2br(e($case->description)) : '—' }}</dd>
            <dt class="col-sm-3">{{ __('Created') }}</dt>
            <dd class="col-sm-9">{{ $case->created_at?->format('Y-m-d H:i') }}</dd>
            <dt class="col-sm-3">{{ __('Updated') }}</dt>
            <dd class="col-sm-9">{{ $case->updated_at?->format('Y-m-d H:i') }}</dd>
          </dl>
        </div>
      </div>
    </div>

    {{-- Sessions — ALOS-S1-12 Court Hearings --}}
    <div class="tab-pane fade {{ $activeTab === 'sessions' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Court Hearings') }}</h5>
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
            <a href="{{ route('admin.core.cases.sessions.index', $case) }}" class="btn btn-outline-secondary btn-sm">{{ __('View all') }}</a>
          </div>
        </div>
        <div class="card-body">
          @php
            $caseSessions = $case->sessions()->with('assignedUser')->orderBy('session_date')->orderBy('session_time')->get();
            $upcoming = $caseSessions->filter(fn ($s) => $s->session_date->isFuture() || ($s->session_date->isToday() && $s->status === 'scheduled'))->take(5);
            $sessionStatusLabels = \App\Models\CaseSession::STATUSES;
          @endphp
          @if($caseSessions->isEmpty())
            <div class="text-center text-muted py-4">
              <i class="icon-base ti tabler-calendar-event icon-32px d-block mb-2 opacity-50"></i>
              <p class="mb-0">{{ __('No sessions yet.') }}</p>
              @can('cases.manage')
                <a href="{{ route('admin.core.cases.sessions.create', $case) }}" class="btn btn-primary btn-sm mt-2">{{ __('Add Session') }}</a>
              @endcan
            </div>
          @else
            <p class="text-muted small mb-3">{{ __('Total') }}: {{ $caseSessions->count() }} {{ __('sessions') }}</p>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Court') }}</th>
                    <th>{{ __('Status') }}</th>
                    @can('cases.manage')<th class="text-end">{{ __('Actions') }}</th>@endcan
                  </tr>
                </thead>
                <tbody>
                  @foreach($upcoming->isEmpty() ? $caseSessions->take(5) : $upcoming as $s)
                    @php $sc = match($s->status) { 'scheduled' => 'primary', 'completed' => 'success', 'cancelled' => 'danger', 'postponed' => 'warning', default => 'secondary' }; @endphp
                    <tr>
                      <td class="text-nowrap">{{ $s->session_date->format('Y-m-d') }}</td>
                      <td class="text-nowrap">{{ $s->session_time ? substr($s->session_time, 0, 5) : '—' }}</td>
                      <td>{{ $s->court_name ?? '—' }}</td>
                      <td><span class="badge bg-label-{{ $sc }}">{{ __($sessionStatusLabels[$s->status] ?? $s->status) }}</span></td>
                      @can('cases.manage')
                      <td class="text-end">
                        <a href="{{ route('admin.core.cases.sessions.edit', [$case, $s]) }}" class="btn btn-icon btn-sm btn-text-primary rounded"><i class="icon-base ti tabler-pencil"></i></a>
                      </td>
                      @endcan
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <a href="{{ route('admin.core.cases.sessions.index', $case) }}" class="btn btn-outline-secondary btn-sm">{{ __('View all sessions') }}</a>
          @endif
        </div>
      </div>
    </div>

    {{-- Documents (placeholder — link to client documents filtered by case) --}}
    <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Documents') }}</h5>
          <a href="{{ route('admin.core.clients.documents.index', ['client' => $case->client, 'case_id' => $case->id]) }}" class="btn btn-primary btn-sm">
            <i class="icon-base ti tabler-folder {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Open Document Center') }}
          </a>
        </div>
        <div class="card-body text-center py-4">
          <p class="text-muted small mb-0">{{ __('Documents linked to this case can be managed from the client Document Center.') }}</p>
        </div>
      </div>
    </div>

    {{-- Messages (placeholder) --}}
    <div class="tab-pane fade {{ $activeTab === 'messages' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="icon-base ti tabler-message icon-32px text-muted d-block mb-3"></i>
          <h6 class="mb-2">{{ __('Messages') }}</h6>
          <p class="text-muted small mb-0">{{ __('This section will be available in a future release.') }}</p>
        </div>
      </div>
    </div>

    {{-- Activity Log (placeholder) --}}
    <div class="tab-pane fade {{ $activeTab === 'activity' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-body text-center py-5">
          <i class="icon-base ti tabler-history icon-32px text-muted d-block mb-3"></i>
          <h6 class="mb-2">{{ __('Activity Log') }}</h6>
          <p class="text-muted small mb-0">{{ __('This section will be available in a future release.') }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
