@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $isRtl = ($contentDir === 'rtl');
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Compliance Log') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Compliance Log') }}</h4>
      <p class="text-muted small mb-0">{{ __('Access violations, unauthorized attempts, failed logins.') }}</p>
    </div>
  </div>

  <div class="offcanvas offcanvas-{{ $isRtl ? 'start' : 'end' }}" tabindex="-1" id="complianceLogFiltersOffcanvas" aria-labelledby="complianceLogFiltersOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="complianceLogFiltersOffcanvasLabel">{{ __('Filters') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      <form action="{{ route('admin.core.compliance-logs.index') }}" method="get" id="complianceLogFiltersForm">
        @if($tenants->isNotEmpty())
        <div class="mb-3">
          <label for="offcanvas_tenant_id" class="form-label">{{ __('Tenant') }}</label>
          <select name="tenant_id" id="offcanvas_tenant_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($tenants as $t)
              <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
          </select>
        </div>
        @endif
        @if(isset($users) && $users->isNotEmpty())
        <div class="mb-3">
          <label for="offcanvas_user_id" class="form-label">{{ __('User') }}</label>
          <select name="user_id" id="offcanvas_user_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="mb-3">
          <label for="offcanvas_attempted_action" class="form-label">{{ __('Attempted action') }}</label>
          <input type="text" name="attempted_action" id="offcanvas_attempted_action" class="form-control" value="{{ request('attempted_action') }}" placeholder="{{ __('e.g. access_client') }}">
        </div>
        <div class="mb-3">
          <label for="offcanvas_target_entity" class="form-label">{{ __('Target entity') }}</label>
          <input type="text" name="target_entity" id="offcanvas_target_entity" class="form-control" value="{{ request('target_entity') }}" placeholder="{{ __('e.g. client') }}">
        </div>
        <div class="mb-3">
          <label for="offcanvas_date_from" class="form-label">{{ __('From date') }}</label>
          <input type="date" name="date_from" id="offcanvas_date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="mb-3">
          <label for="offcanvas_date_to" class="form-label">{{ __('To date') }}</label>
          <input type="date" name="date_to" id="offcanvas_date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="mb-4">
          <label for="offcanvas_per_page" class="form-label">{{ __('Per page') }}</label>
          <select name="per_page" id="offcanvas_per_page" class="form-select">
            @foreach([10, 20, 50, 100] as $n)
              <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }}</option>
            @endforeach
          </select>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Filter') }}</button>
          <a href="{{ route('admin.core.compliance-logs.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0">{{ __('Compliance entries') }}</h5>
      <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#complianceLogFiltersOffcanvas">
        <i class="icon-base ti tabler-filter {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Filters') }}
      </button>
    </div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Tenant') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Attempted action') }}</th>
            <th>{{ __('Target') }}</th>
            <th>{{ __('Description') }}</th>
            <th class="text-nowrap" style="min-width: 5rem;">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td class="text-nowrap small">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
              <td><span class="text-muted small">{{ $log->tenant?->name ?? '—' }}</span></td>
              <td><span class="text-muted small">{{ $log->user?->name ?? $log->user_id ?? '—' }}</span></td>
              <td><code class="small">{{ $log->attempted_action }}</code></td>
              <td><span class="small">{{ $log->target_entity }} @if($log->target_id)#{{ $log->target_id }}@endif</span></td>
              <td><span class="small text-truncate d-inline-block" style="max-width: 200px;" title="{{ $log->description }}">{{ Str::limit($log->description, 50) }}</span></td>
              <td class="text-nowrap">
                <a href="{{ route('admin.core.compliance-logs.show', $log) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                  <i class="icon-base ti tabler-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="icon-base ti tabler-shield-exclamation icon-32px d-block mb-2 opacity-50"></i>
                {{ __('No compliance log entries yet.') }}
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($logs->hasPages())
      <div class="card-footer d-flex justify-content-center">
        {{ $logs->withQueryString()->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
