@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $isRtl = ($contentDir === 'rtl');
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Audit Log') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Audit Log') }}</h4>
      <p class="text-muted small mb-0">{{ __('History of major updates: case status, documents, sessions, consultations.') }}</p>
    </div>
  </div>

  {{-- لوحة الفلاتر من الجنب (Offcanvas) --}}
  <div class="offcanvas offcanvas-{{ $isRtl ? 'start' : 'end' }}" tabindex="-1" id="auditLogFiltersOffcanvas" aria-labelledby="auditLogFiltersOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="auditLogFiltersOffcanvasLabel">{{ __('Filters') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      <form action="{{ route('admin.core.audit-logs.index') }}" method="get" id="auditLogFiltersForm">
        <div class="mb-3">
          <label for="offcanvas_tenant_id" class="form-label">{{ __('Tenant') }}</label>
          <select name="tenant_id" id="offcanvas_tenant_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($tenants as $t)
              <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label for="offcanvas_entity_type" class="form-label">{{ __('Entity type') }}</label>
          <select name="entity_type" id="offcanvas_entity_type" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($entityTypes as $et)
              <option value="{{ $et }}" {{ request('entity_type') === $et ? 'selected' : '' }}>{{ $et }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label for="offcanvas_action" class="form-label">{{ __('Action') }}</label>
          <input type="text" name="action" id="offcanvas_action" class="form-control" value="{{ request('action') }}" placeholder="{{ __('e.g. update_case') }}">
        </div>
        @if(isset($users) && $users->isNotEmpty())
        <div class="mb-3">
          <label for="offcanvas_user_id" class="form-label">{{ __('User') }}</label>
          <select name="user_id" id="offcanvas_user_id" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach($users as $u)
              <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} @if($u->email)({{ $u->email }})@endif</option>
            @endforeach
          </select>
        </div>
        @endif
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
          <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="icon-base ti tabler-filter {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Filter') }}
          </button>
          <a href="{{ route('admin.core.audit-logs.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0">{{ __('Log entries') }}</h5>
      <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#auditLogFiltersOffcanvas" aria-controls="auditLogFiltersOffcanvas">
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
            <th>{{ __('Action') }}</th>
            <th>{{ __('Entity') }}</th>
            <th>{{ __('IP') }}</th>
            <th class="text-nowrap" style="min-width: 5rem;">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td class="text-nowrap small">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
              <td><span class="text-muted small">{{ $log->tenant?->name ?? '—' }}</span></td>
              <td><span class="text-muted small">{{ $log->admin?->name ?? $log->user?->name ?? ($log->admin_user_id ? '#' . $log->admin_user_id : '—') }}</span></td>
              <td><code class="small">{{ $log->action }}</code></td>
              <td><span class="small">{{ $log->entity_type }} @if($log->entity_id)#{{ $log->entity_id }}@endif</span></td>
              <td><span class="text-muted small">{{ $log->ip_address }}</span></td>
              <td class="text-nowrap">
                <a href="{{ route('admin.core.audit-logs.show', $log) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                  <i class="icon-base ti tabler-eye"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-5">
                <i class="icon-base ti tabler-history icon-32px d-block mb-2 opacity-50"></i>
                {{ __('No audit log entries yet.') }}
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
