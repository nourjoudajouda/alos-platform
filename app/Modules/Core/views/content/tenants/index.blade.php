@php
  $crudIndexId = 'law-firms';
  $crudIndexTitle = __('Law Firms') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.tenants.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Law Firms');
  $crudIndexAddUrl = route('admin.core.tenants.create');
  $crudIndexAddLabel = __('Add Law Firm');
  $crudIndexEmptyMessage = __('No law firms yet.');
  $crudIndexEmptyLink = route('admin.core.tenants.create');
  $crudIndexEmptyLinkText = __('Add Law Firm');
  $crudIndexShowViewToggle = true;
  $items = $tenants;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $totalTenants = $totalTenants ?? 0;
  $activeCount = $activeCount ?? 0;
  $suspendedCount = $suspendedCount ?? 0;
  $inactiveCount = $inactiveCount ?? 0;
  $recentTenants = $recentTenants ?? 0;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Law firms subscribe to the platform. Here you manage all law firms, subscription plans and contract details.') }}
@endsection

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Law Firms'), 'value' => $totalTenants, 'subtitle' => __('Law Firms'), 'icon' => 'ti tabler-building-store', 'bgLabel' => 'primary'])
  @include('core::_partials.crud-stat-card', ['title' => __('Active'), 'value' => $activeCount, 'subtitle' => __('Law Firms'), 'icon' => 'ti tabler-circle-check', 'bgLabel' => 'success'])
  @include('core::_partials.crud-stat-card', ['title' => __('Suspended'), 'value' => $suspendedCount, 'subtitle' => __('Law Firms'), 'icon' => 'ti tabler-player-pause', 'bgLabel' => 'warning'])
  @include('core::_partials.crud-stat-card', ['title' => __('Last 30 days'), 'value' => $recentTenants, 'subtitle' => __('New'), 'icon' => 'ti tabler-calendar', 'bgLabel' => 'info'])
@endsection

@section('crud_filters_hidden_inputs')
  @if(request('plan'))<input type="hidden" name="plan" value="{{ request('plan') }}">@endif
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
  @if(request('date_from'))<input type="hidden" name="date_from" value="{{ request('date_from') }}">@endif
  @if(request('date_to'))<input type="hidden" name="date_to" value="{{ request('date_to') }}">@endif
@endsection

@section('crud_offcanvas')
  <form action="{{ route('admin.core.tenants.index') }}" method="get" id="filtersFormSide">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    @if(request('view'))<input type="hidden" name="view" value="{{ request('view') }}">@endif
    <div class="mb-3">
      <label for="filterDateFrom" class="form-label">{{ __('From date') }}</label>
      <input type="date" name="date_from" id="filterDateFrom" class="form-control" value="{{ $filterDateFrom ?? '' }}">
      <div class="form-text small">{{ __('Filter law firms created from this date') }}</div>
    </div>
    <div class="mb-3">
      <label for="filterDateTo" class="form-label">{{ __('To date') }}</label>
      <input type="date" name="date_to" id="filterDateTo" class="form-control" value="{{ $filterDateTo ?? '' }}">
      <div class="form-text small">{{ __('Filter law firms created until this date') }}</div>
    </div>
    <div class="mb-3">
      <label for="filterPlan" class="form-label">{{ __('Filter by Plan') }}</label>
      <select name="plan" id="filterPlan" class="form-select">
        <option value="">{{ __('All') }}</option>
        @foreach(\App\Models\Tenant::PLANS as $p)
          <option value="{{ $p }}" {{ ($filterPlan ?? '') === $p ? 'selected' : '' }}>{{ __(ucfirst($p)) }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label for="filterStatus" class="form-label">{{ __('Filter by Status') }}</label>
      <select name="status" id="filterStatus" class="form-select">
        <option value="">{{ __('All') }}</option>
        <option value="active" {{ ($filterStatus ?? '') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
        <option value="suspended" {{ ($filterStatus ?? '') === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
        <option value="inactive" {{ ($filterStatus ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
      </select>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Search') }}</button>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('Close') }}</button>
    </div>
  </form>
@endsection

@section('crud_table_header')
  <th>{{ __('Company name') }}</th>
  <th>{{ __('Email') }}</th>
  <th>{{ __('Plan') }}</th>
  <th>{{ __('Contract dates') }}</th>
  <th>{{ __('Status') }}</th>
  <th class="text-nowrap" style="min-width: 8rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($tenants as $tenant)
    @php
      $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z0-9\p{Arabic}]/u', '', $tenant->name), 0, 2) ?: $tenant->slug);
      if (mb_strlen($initials) > 2) $initials = mb_substr($initials, 0, 2);
      $status = $tenant->status ?? \App\Models\Tenant::STATUS_ACTIVE;
    @endphp
    <tr>
      <td>
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span>
          </span>
          <div>
            <a href="{{ route('admin.core.tenants.show', $tenant) }}" class="fw-medium text-body d-block">{{ $tenant->name }}</a>
            <span class="text-muted small">{{ $tenant->slug }}</span>
          </div>
        </div>
      </td>
      <td><span class="text-break">{{ $tenant->email ?? '—' }}</span></td>
      <td>
        @if($tenant->subscriptionPlan)
          <span class="badge bg-label-primary">{{ $tenant->subscriptionPlan->plan_name }}</span>
          @if($tenant->subscriptionPlan->price !== null)
            <span class="small text-muted d-block">{{ number_format($tenant->subscriptionPlan->price, 2) }}</span>
          @endif
        @else
          <span class="badge bg-label-secondary">—</span>
        @endif
      </td>
      <td>
        @if($tenant->contract_start_date || $tenant->contract_end_date)
          <span class="text-nowrap small">{{ $tenant->contract_start_date?->format('Y-m-d') ?? '—' }} – {{ $tenant->contract_end_date?->format('Y-m-d') ?? '—' }}</span>
        @else
          <span class="text-muted">—</span>
        @endif
      </td>
      <td>
        @if($status === 'active')
          <span class="badge bg-label-success">{{ __('Active') }}</span>
        @elseif($status === 'suspended')
          <span class="badge bg-label-warning">{{ __('Suspended') }}</span>
        @else
          <span class="badge bg-label-secondary">{{ __('Inactive') }}</span>
        @endif
      </td>
      <td class="text-nowrap">
        <div class="table-actions d-flex align-items-center gap-1">
          <a href="{{ route('admin.core.tenants.show', $tenant) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
            <i class="icon-base ti tabler-eye"></i>
          </a>
          <a href="{{ route('admin.core.tenants.edit', $tenant) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          @if($status !== 'suspended')
            <form action="{{ route('admin.core.tenants.suspend', $tenant) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Suspend this law firm?') }}');">
              @csrf
              <button type="submit" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Suspend') }}">
                <i class="icon-base ti tabler-player-pause"></i>
              </button>
            </form>
          @else
            <form action="{{ route('admin.core.tenants.activate', $tenant) }}" method="post" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-icon btn-sm btn-text-success rounded" title="{{ __('Activate') }}">
                <i class="icon-base ti tabler-player-play"></i>
              </button>
            </form>
          @endif
          <form action="{{ route('admin.core.tenants.destroy', $tenant) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this law firm?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}">
              <i class="icon-base ti tabler-trash"></i>
            </button>
          </form>
        </div>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="6" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-building-store icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection

@section('crud_extra_script')
<script>
(function() {
  var formSide = document.getElementById('filtersFormSide');
  if (formSide) {
    document.querySelectorAll('#filterPlan, #filterStatus, #filterDateFrom, #filterDateTo').forEach(function(el) {
      if (el) el.addEventListener('change', function() { formSide.submit(); });
    });
  }
})();
</script>
@endsection
