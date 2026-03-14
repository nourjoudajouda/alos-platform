@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $summary = $summary ?? [];
  $m = $summary['metrics'] ?? [];
  $myCases = $summary['my_cases'] ?? [];
  $recentMessages = $summary['recent_messages'] ?? [];
  $sharedDocuments = $summary['shared_documents'] ?? [];
  $upcomingSessions = $summary['upcoming_sessions'] ?? [];
  $recentReports = $summary['recent_reports'] ?? [];
@endphp

@extends('portal::layouts.portal')

@section('title', __('Dashboard') . ' — ' . __('Client Portal'))

@section('content')
  <div class="row mb-4">
    <div class="col-12">
      <h4 class="fw-bold mb-1">{{ __('Dashboard') }}</h4>
      <p class="text-muted mb-0">{{ __('Welcome') }}, {{ $user->name }}</p>
    </div>
  </div>

  {{-- Quick Links --}}
  <div class="card mb-4">
    <div class="card-body py-3">
      <h6 class="text-muted small text-uppercase mb-2">{{ __('Quick Links') }}</h6>
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('portal.cases.index') }}" class="btn btn-sm btn-primary">
          <i class="icon-base ti tabler-briefcase me-1"></i>{{ __('View My Cases') }}
        </a>
        <a href="{{ route('portal.messages.index') }}" class="btn btn-sm btn-outline-primary">
          <i class="icon-base ti tabler-mail me-1"></i>{{ __('Open Messages') }}</a>
        <a href="{{ route('portal.documents.index') }}" class="btn btn-sm btn-outline-primary">
          <i class="icon-base ti tabler-file-text me-1"></i>{{ __('View Documents') }}</a>
        <a href="{{ route('portal.reports.index') }}" class="btn btn-sm btn-outline-primary">
          <i class="icon-base ti tabler-report me-1"></i>{{ __('View Reports') }}</a>
        <a href="{{ route('portal.consultations.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="icon-base ti tabler-messages me-1"></i>{{ __('Consultations') }}</a>
      </div>
    </div>
  </div>

  {{-- Summary Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-primary d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-briefcase icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['cases_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Cases') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-success d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-lock-open icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['open_cases_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Open') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-info d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-messages icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['consultations_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Consultations') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-warning d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-file-text icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['shared_documents_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Documents') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-primary d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-mail icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['message_threads_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Threads') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-secondary d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-mail-opened icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['unread_messages_approx'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('New (7d)') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-success d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-report icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['reports_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Reports') }}</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="card h-100">
        <div class="card-body d-flex align-items-center gap-2">
          <span class="avatar avatar-md rounded-circle bg-label-info d-flex align-items-center justify-content-center"><i class="icon-base ti tabler-calendar-event icon-20px"></i></span>
          <div>
            <h5 class="mb-0">{{ $m['upcoming_sessions_count'] ?? 0 }}</h5>
            <small class="text-muted">{{ __('Upcoming Sessions') }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Widgets: My Cases, Recent Messages | Shared Documents, Upcoming Sessions, Recent Reports --}}
  <div class="row g-4">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-briefcase me-2"></i>{{ __('My Cases') }}</h5>
          <a href="{{ route('portal.cases.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($myCases) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Case number') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Last update') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($myCases as $c)
                    <tr>
                      <td>{{ $c['case_number'] ?? '—' }}</td>
                      <td>{{ $c['case_type'] ?? '—' }}</td>
                      <td><span class="badge bg-label-{{ $c['status'] === 'open' ? 'success' : ($c['status'] === 'closed' ? 'secondary' : 'warning') }}">{{ __($c['status'] ?? '') }}</span></td>
                      <td><small class="text-muted">{{ $c['updated_at_human'] ?? $c['updated_at'] }}</small></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="text-center py-5 px-3">
              <i class="icon-base ti tabler-briefcase icon-32px text-muted d-block mb-2"></i>
              <p class="text-muted mb-0">{{ __('No cases yet.') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-mail me-2"></i>{{ __('Recent Messages') }}</h5>
          <a href="{{ route('portal.messages.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentMessages) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($recentMessages as $msg)
                <a href="{{ route('portal.messages.show', $msg['id']) }}" class="list-group-item list-group-item-action text-body text-decoration-none">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <strong>{{ $msg['subject'] ?? __('Message') }}</strong>
                      @if (!empty($msg['has_new_from_office']))
                        <span class="badge bg-primary ms-1">{{ __('New') }}</span>
                      @endif
                    </div>
                    <small class="text-muted">{{ $msg['last_message_at'] ?? '' }}</small>
                  </div>
                  @if (!empty($msg['last_message_body']))
                    <p class="mb-0 mt-1 small text-body opacity-75">{{ $msg['last_message_body'] }}</p>
                  @endif
                </a>
              @endforeach
            </ul>
          @else
            <div class="text-center py-5 px-3">
              <i class="icon-base ti tabler-mail icon-32px text-muted d-block mb-2"></i>
              <p class="text-muted mb-0">{{ __('No recent messages.') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-file-text me-2"></i>{{ __('Shared Documents') }}</h5>
          <a href="{{ route('portal.documents.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($sharedDocuments) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($sharedDocuments as $doc)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <a href="{{ route('portal.documents.download', $doc['id']) }}" class="fw-medium text-body text-decoration-none" target="_blank" rel="noopener">{{ $doc['name'] ?? __('Document') }}</a>
                  <div class="d-flex align-items-center gap-2">
                    @if (!empty($doc['mime_type']))
                      <span class="badge bg-label-secondary small">{{ \Illuminate\Support\Str::afterLast($doc['mime_type'], '/') ?: __('File') }}</span>
                    @endif
                    <small class="text-muted">{{ $doc['updated_at_human'] ?? $doc['updated_at'] }}</small>
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div class="text-center py-5 px-3">
              <i class="icon-base ti tabler-file-text icon-32px text-muted d-block mb-2"></i>
              <p class="text-muted mb-0">{{ __('No shared documents.') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-calendar-event me-2"></i>{{ __('Upcoming Sessions') }}</h5>
          <a href="{{ route('portal.cases.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View cases') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($upcomingSessions) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($upcomingSessions as $s)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <span class="fw-medium">{{ $s['session_date_formatted'] ?? $s['session_date'] }}</span>
                    <span class="text-muted ms-2">{{ $s['session_time'] ?? '' }}</span>
                    @if (!empty($s['court_name']))
                      <br><small class="text-muted">{{ $s['court_name'] }}</small>
                    @endif
                    @if (!empty($s['case_number']))
                      <br><small>{{ __('Case') }}: {{ $s['case_number'] }}</small>
                    @endif
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div class="text-center py-5 px-3">
              <i class="icon-base ti tabler-calendar-event icon-32px text-muted d-block mb-2"></i>
              <p class="text-muted mb-0">{{ __('No upcoming sessions.') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="icon-base ti tabler-report me-2"></i>{{ __('Recent Reports') }}</h5>
          <a href="{{ route('portal.reports.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentReports) > 0)
            <ul class="list-group list-group-flush">
              @foreach ($recentReports as $r)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <a href="{{ route('portal.reports.show', $r['id']) }}" class="fw-medium">{{ $r['title'] ?? __('Report') }}</a>
                    @if (!empty($r['report_type']))
                      <span class="badge bg-label-secondary ms-2">{{ $r['report_type'] }}</span>
                    @endif
                  </div>
                  <small class="text-muted">{{ $r['generated_at_human'] ?? $r['generated_at'] }}</small>
                </li>
              @endforeach
            </ul>
          @else
            <div class="text-center py-5 px-3">
              <i class="icon-base ti tabler-report icon-32px text-muted d-block mb-2"></i>
              <p class="text-muted mb-0">{{ __('No reports available.') }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- My details (compact) --}}
  <div class="row mt-4">
    <div class="col-md-6 col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('My details') }}</h5>
        </div>
        <div class="card-body">
          <dl class="mb-0 small">
            <dt>{{ __('Name') }}</dt>
            <dd>{{ $client->name }}</dd>
            @if ($client->email)
              <dt>{{ __('Email') }}</dt>
              <dd>{{ $client->email }}</dd>
            @endif
            @if ($client->phone)
              <dt>{{ __('Phone') }}</dt>
              <dd>{{ $client->phone }}</dd>
            @endif
          </dl>
        </div>
      </div>
    </div>
  </div>
@endsection
