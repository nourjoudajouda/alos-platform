@extends('core::layouts.layoutMaster')

@section('title', __('Office Dashboard') . ' — ' . (auth()->user()->tenant?->name ?? config('app.name')))

@section('content')
@php
  $summary = $summary ?? [];
  $m = $summary['metrics'] ?? [];
  $upcomingSessions = $summary['upcoming_sessions'] ?? [];
  $recentMessages = $summary['recent_messages'] ?? [];
  $recentDocuments = $summary['recent_documents'] ?? [];
  $recentActivity = $summary['recent_activity'] ?? [];
@endphp
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Office Dashboard') }}</h4>
      <p class="text-body mb-0 small">{{ __('Key metrics and recent activity for your office.') }}</p>
    </div>
  </div>

  {{-- Quick Actions --}}
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body py-3">
          <h6 class="card-title mb-2 text-muted small text-uppercase">{{ __('Quick Actions') }}</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('company.clients.create') }}" class="btn btn-sm btn-primary">
              <i class="icon-base ti tabler-user-plus me-1"></i>{{ __('Add Client') }}
            </a>
            <a href="{{ route('company.cases.create') }}" class="btn btn-sm btn-outline-primary">
              <i class="icon-base ti tabler-briefcase me-1"></i>{{ __('Add Case') }}
            </a>
            <a href="{{ route('company.consultations.create') }}" class="btn btn-sm btn-outline-primary">
              <i class="icon-base ti tabler-messages me-1"></i>{{ __('Add Consultation') }}
            </a>
            <a href="{{ route('company.cases.index') }}" class="btn btn-sm btn-outline-primary">
              <i class="icon-base ti tabler-calendar-event me-1"></i>{{ __('Add Session') }}
            </a>
            <a href="{{ route('company.clients.index') }}" class="btn btn-sm btn-outline-secondary">
              <i class="icon-base ti tabler-mail me-1"></i>{{ __('Open Messages') }}
            </a>
            <a href="{{ route('company.clients.index') }}" class="btn btn-sm btn-outline-secondary">
              <i class="icon-base ti tabler-report me-1"></i>{{ __('Open Reports') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Finance module (ALOS-S1-29B): only show when plan has finance_module feature --}}
  @if ($hasFinanceModule ?? false)
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="card border-primary">
        <div class="card-body py-3 d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded bg-label-primary d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-file-invoice icon-24px"></i>
          </span>
          <div>
            <h6 class="mb-0">{{ __('Finance') }}</h6>
            <small class="text-muted">{{ __('Invoices, Payments & Financial Reports') }}</small>
          </div>
          <span class="badge bg-label-info ms-auto">{{ __('Available in your plan') }}</span>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Metric Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-primary d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-users icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['total_clients'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Clients') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-info d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-briefcase icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['total_cases'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Cases') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-success d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-lock-open icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['open_cases'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Open') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-secondary d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-lock icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['closed_cases'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Closed') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-warning d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-messages icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['consultations_count'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Consultations') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-dark d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-file-text icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['documents_count'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Documents') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-primary d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-mail icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['message_threads_count'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Threads') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-info d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-mail-opened icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['unread_messages_approx'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('New (7d)') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-success d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-calendar-event icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['upcoming_sessions_count'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Upcoming Sessions') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-warning d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-report icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['reports_pending_count'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Reports pending') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="avatar avatar-lg rounded-circle bg-label-danger d-flex align-items-center justify-content-center">
            <i class="icon-base ti tabler-bell icon-24px"></i>
          </span>
          <div>
            <h4 class="mb-0">{{ $m['unread_notifications_count'] ?? 0 }}</h4>
            <small class="text-muted">{{ __('Unread notifications') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  @php
    $totalCasesForChart = ($m['total_cases'] ?? 0) ?: 1;
    $openPct = (($m['open_cases'] ?? 0) / $totalCasesForChart) * 100;
    $closedPct = (($m['closed_cases'] ?? 0) / $totalCasesForChart) * 100;
    $pendingPct = 100 - $openPct - $closedPct;
    $pendingCount = ($m['total_cases'] ?? 0) - ($m['open_cases'] ?? 0) - ($m['closed_cases'] ?? 0);
  @endphp
  <div class="row g-3 mb-4">
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h6 class="text-muted small text-uppercase mb-3">{{ __('Cases by status') }}</h6>
          <div class="progress mb-3" style="height: 20px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $openPct }}%;" title="{{ __('Open') }}: {{ $m['open_cases'] ?? 0 }}"></div>
            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPct }}%;" title="{{ __('Pending') }}: {{ $pendingCount }}"></div>
            <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $closedPct }}%;" title="{{ __('Closed') }}: {{ $m['closed_cases'] ?? 0 }}"></div>
          </div>
          <div class="d-flex flex-wrap gap-3 small">
            <span><span class="badge bg-label-success"> </span> {{ __('Open') }}: {{ $m['open_cases'] ?? 0 }}</span>
            <span><span class="badge bg-label-warning"> </span> {{ __('Pending') }}: {{ $pendingCount }}</span>
            <span><span class="badge bg-label-secondary"> </span> {{ __('Closed') }}: {{ $m['closed_cases'] ?? 0 }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Two columns: Sessions + Messages | Documents + Activity --}}
  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-calendar-event me-2"></i>{{ __('Upcoming Sessions') }}</h5>
          <a href="{{ route('company.cases.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($upcomingSessions) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Time') }}</th>
                    <th>{{ __('Court') }}</th>
                    <th>{{ __('Client / Case') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($upcomingSessions as $s)
                    <tr>
                      <td>{{ $s['session_date_formatted'] ?? $s['session_date'] }}</td>
                      <td>{{ $s['session_time'] ?? '—' }}</td>
                      <td>{{ $s['court_name'] ?? '—' }}</td>
                      <td>
                        <span class="d-block">{{ $s['client_name'] ?? '—' }}</span>
                        @if (!empty($s['case_number']))
                          <small class="text-muted">{{ $s['case_number'] }}</small>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted p-4 mb-0">{{ __('No upcoming sessions.') }}</p>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-mail me-2"></i>{{ __('Recent Messages') }}</h5>
          <a href="{{ route('company.clients.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentMessages) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($recentMessages as $msg)
                <li class="list-group-item list-group-item-action">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <strong>{{ $msg['client_name'] ?? __('Client') }}</strong>
                      @if (!empty($msg['subject']))
                        <span class="text-muted">— {{ \Str::limit($msg['subject'], 40) }}</span>
                      @endif
                    </div>
                    <small class="text-muted">{{ $msg['last_message_at'] ?? '' }}</small>
                  </div>
                  @if (!empty($msg['last_message_body']))
                    <p class="mb-0 mt-1 small text-body">{{ $msg['last_message_body'] }}</p>
                  @endif
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-muted p-4 mb-0">{{ __('No recent messages.') }}</p>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-file-text me-2"></i>{{ __('Recent Documents') }}</h5>
          <a href="{{ route('company.clients.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentDocuments) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($recentDocuments as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <span class="fw-medium">{{ $doc['name'] ?? __('Document') }}</span>
                    <br><small class="text-muted">{{ $doc['client_name'] ?? '' }}</small>
                  </div>
                  <small class="text-muted">{{ $doc['created_at_human'] ?? $doc['created_at'] }}</small>
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-muted p-4 mb-0">{{ __('No recent documents.') }}</p>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-activity me-2"></i>{{ __('Recent Activity') }}</h5>
          <a href="{{ route('company.notifications.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentActivity) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($recentActivity as $act)
                <li class="list-group-item d-flex justify-content-between align-items-start">
                  <div>
                    <span class="text-body">{{ $act['description'] ?? $act['action'] }}</span>
                    @if (!empty($act['user_name']))
                      <br><small class="text-muted">{{ $act['user_name'] }}</small>
                    @endif
                  </div>
                  <small class="text-muted">{{ $act['created_at_human'] ?? '' }}</small>
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-muted p-4 mb-0">{{ __('No recent activity.') }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
