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

  {{-- ALOS-S1-31B — Platform metrics only (no clients, cases, consultations) --}}
  @php
    $m = $metrics ?? [];
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
