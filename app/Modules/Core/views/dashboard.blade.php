@extends('core::layouts.layoutMaster')

@section('title', __('Dashboard') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Dashboard') }}</h4>
      <p class="text-body mb-0 small">{{ __('Platform overview: law firms, subscriptions and contracts') }}</p>
    </div>
  </div>

  {{-- ALOS-S1-31B — Platform metrics only (no clients, cases, consultations, documents, messages) --}}
  @php
    $summary = $summary ?? [];
    $m = $summary['metrics'] ?? [];
  @endphp
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Total Law Firms') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['total_law_firms'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('Registered offices') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-building-store icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Active Law Firms') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['active_law_firms'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('Can access the platform') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base ti tabler-building icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Suspended Law Firms') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['suspended_law_firms'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('Inactive or suspended') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="icon-base ti tabler-building-skyscraper icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Active Subscriptions') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['active_subscriptions'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('With a plan assigned') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base ti tabler-credit-card icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Expiring Contracts') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['expiring_contracts'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('Within 30 days') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base ti tabler-calendar-due icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Expired Contracts') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['expired_contracts'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('Past end date') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="icon-base ti tabler-calendar-off icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-4 col-xxl-2">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Subscription Plans') }}</p>
            <h3 class="mb-0 fw-bold">{{ $m['total_subscription_plans'] ?? 0 }}</h3>
            <p class="mb-0 mt-2 small text-muted">{{ __('Available plans') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-list icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- A) Recently Registered Firms --}}
  @php $recentFirms = $summary['recently_registered_firms'] ?? []; @endphp
  <div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Recently Registered Firms') }}</h5>
          <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentFirms) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Plan') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Created') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($recentFirms as $firm)
                    <tr>
                      <td><a href="{{ route('admin.core.tenants.show', $firm['id']) }}">{{ $firm['name'] }}</a></td>
                      <td>{{ $firm['email'] }}</td>
                      <td>{{ $firm['plan_name'] }}</td>
                      <td><span class="badge bg-{{ !empty($firm['is_active']) ? 'success' : 'secondary' }}">{{ $firm['status'] }}</span></td>
                      <td class="text-muted small">{{ $firm['created_at_human'] ?? $firm['created_at'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-0 p-4">{{ __('No recently registered firms.') }}</p>
          @endif
        </div>
      </div>
    </div>

    {{-- B) Expiring Contracts --}}
    @php $expiringList = $summary['expiring_contracts_list'] ?? []; @endphp
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Expiring Contracts') }}</h5>
          <a href="{{ route('admin.core.contracts.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($expiringList) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Contract end') }}</th>
                    <th>{{ __('Plan') }}</th>
                    <th>{{ __('Status') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($expiringList as $item)
                    <tr>
                      <td><a href="{{ route('admin.core.tenants.show', $item['id']) }}">{{ $item['name'] }}</a></td>
                      <td>{{ $item['contract_end_date'] }} <span class="text-muted small">({{ $item['contract_end_date_human'] }})</span></td>
                      <td>{{ $item['plan_name'] }}</td>
                      <td><span class="badge bg-{{ $item['status'] === 'active' || $item['status'] === 'trial' ? 'success' : 'secondary' }}">{{ ucfirst($item['status'] ?? '—') }}</span></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-0 p-4">{{ __('No contracts expiring in the next 14 days.') }}</p>
          @endif
        </div>
      </div>
    </div>

    {{-- B2) Expired Contracts --}}
    @php $expiredList = $summary['expired_contracts_list'] ?? []; @endphp
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Expired Contracts') }}</h5>
          <a href="{{ route('admin.core.contracts.index', ['expired' => '1']) }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($expiredList) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Company') }}</th>
                    <th>{{ __('Plan') }}</th>
                    <th>{{ __('Contract end') }}</th>
                    <th>{{ __('Status') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($expiredList as $item)
                    <tr>
                      <td><a href="{{ route('admin.core.tenants.show', $item['id']) }}">{{ $item['name'] }}</a></td>
                      <td>{{ $item['plan_name'] }}</td>
                      <td class="text-muted">{{ $item['contract_end_date'] }}</td>
                      <td><span class="badge bg-danger">{{ ucfirst($item['status'] ?? 'expired') }}</span></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-0 p-4">{{ __('No expired contracts.') }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ALOS-S1-38 — Security Overview Widget --}}
  @php $security = $summary['security'] ?? []; @endphp
  <div class="row g-4 mb-4">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Recent Admin Logins') }}</h5>
          <a href="{{ route('admin.core.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Audit Logs') }}</a>
        </div>
        <div class="card-body p-0">
          @php $recentLogins = $security['recent_admin_logins'] ?? []; @endphp
          @if (count($recentLogins) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Admin') }}</th>
                    <th>{{ __('IP') }}</th>
                    <th>{{ __('Time') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($recentLogins as $log)
                    <tr>
                      <td>{{ $log['admin_name'] ?? '—' }}</td>
                      <td><code class="small">{{ $log['ip_address'] ?? '—' }}</code></td>
                      <td class="text-muted small">{{ $log['login_time_human'] ?? $log['login_time'] ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-0 p-4">{{ __('No recent admin logins.') }}</p>
          @endif
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Security Overview') }}</h5>
          <a href="{{ route('admin.core.compliance-logs.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Compliance Logs') }}</a>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar flex-shrink-0">
              <span class="avatar-initial rounded bg-{{ ($security['failed_logins_24h'] ?? 0) > 0 ? 'danger' : 'success' }}">
                <i class="icon-base ti tabler-shield-check icon-24px"></i>
              </span>
            </div>
            <div>
              <p class="mb-0 fw-bold">{{ __('Failed logins (24h)') }}</p>
              <h4 class="mb-0">{{ $security['failed_logins_24h'] ?? 0 }}</h4>
            </div>
          </div>
          @php $securityEvents = $security['security_events'] ?? []; @endphp
          @if (count($securityEvents) > 0)
            <p class="text-muted small mb-2">{{ __('Recent security events:') }}</p>
            <ul class="list-unstyled mb-0 small">
              @foreach (array_slice($securityEvents, 0, 3) as $ev)
                <li class="text-muted py-1">{{ $ev['description'] ?? $ev['action'] ?? '—' }} <span class="text-muted">({{ $ev['created_at_human'] ?? '' }})</span></li>
              @endforeach
            </ul>
          @else
            <p class="text-muted small mb-0">{{ __('No recent security events.') }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- C) Recent Platform Activity --}}
  @php $recentActivity = $summary['recent_platform_activity'] ?? []; @endphp
  <div class="row g-4 mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Recent Platform Activity') }}</h5>
          <a href="{{ route('admin.core.audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Audit Logs') }}</a>
        </div>
        <div class="card-body p-0">
          @if (count($recentActivity) > 0)
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th>{{ __('Admin / User') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Entity') }}</th>
                    <th>{{ __('Date') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($recentActivity as $log)
                    <tr>
                      <td>{{ $log['user_name'] }}</td>
                      <td>{{ $log['description'] }}</td>
                      <td>{{ $log['entity_type'] }} @if($log['entity_id']) #{{ $log['entity_id'] }} @endif</td>
                      <td class="text-muted small">{{ $log['created_at_human'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <p class="text-muted mb-0 p-4">{{ __('No recent platform activity.') }}</p>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Quick links') }}</h5>
        </div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Law Firms') }}</a>
            <a href="{{ route('admin.core.subscription-plans.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Subscription Plans') }}</a>
            <a href="{{ route('admin.core.contracts.index') }}" class="btn btn-outline-primary btn-sm">{{ __('Contracts') }}</a>
            <a href="{{ route('admin.core.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Audit Logs') }}</a>
            <a href="{{ route('admin.core.system-settings.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('System Settings') }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
