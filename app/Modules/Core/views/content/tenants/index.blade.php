@php
  $crudIndexId = 'tenants';
  $crudIndexTitle = __('Tenants') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.tenants.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Tenants');
  $crudIndexAddUrl = route('admin.core.tenants.create');
  $crudIndexAddLabel = __('Add Tenant');
  $crudIndexEmptyMessage = __('No tenants yet.');
  $crudIndexEmptyLink = route('admin.core.tenants.create');
  $crudIndexEmptyLinkText = __('Add Tenant');
  $crudIndexShowViewToggle = true;
  $items = $tenants;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $totalTenants = $totalTenants ?? 0;
  $tenantsWithUsers = $tenantsWithUsers ?? 0;
  $recentTenants = $recentTenants ?? 0;
  $withoutUsers = $totalTenants - $tenantsWithUsers;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Tenants are the law firms / organizations. Each tenant is one office. Here you manage all tenants in the platform.') }}
@endsection

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Tenants'), 'value' => $totalTenants, 'subtitle' => __('Tenants'), 'icon' => 'ti tabler-building-store', 'bgLabel' => 'primary'])
  @include('core::_partials.crud-stat-card', ['title' => __('With Users'), 'value' => $tenantsWithUsers, 'subtitle' => __('Tenants'), 'icon' => 'ti tabler-users', 'bgLabel' => 'success'])
  @include('core::_partials.crud-stat-card', ['title' => __('Last 30 days'), 'value' => $recentTenants, 'subtitle' => __('Tenants'), 'icon' => 'ti tabler-calendar', 'bgLabel' => 'info'])
  @include('core::_partials.crud-stat-card', ['title' => __('Without Users'), 'value' => $withoutUsers, 'subtitle' => __('Tenants'), 'icon' => 'ti tabler-user-off', 'bgLabel' => 'secondary'])
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
      <div class="form-text small">{{ __('Filter tenants created from this date') }}</div>
    </div>
    <div class="mb-3">
      <label for="filterDateTo" class="form-label">{{ __('To date') }}</label>
      <input type="date" name="date_to" id="filterDateTo" class="form-control" value="{{ $filterDateTo ?? '' }}">
      <div class="form-text small">{{ __('Filter tenants created until this date') }}</div>
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
        <option value="pending" {{ ($filterStatus ?? '') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
      </select>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Search') }}</button>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('Close') }}</button>
    </div>
  </form>
@endsection

@section('crud_table_header')
  <th>{{ __('Name') }}</th>
  <th>{{ __('Created At') }}</th>
  <th>{{ __('Plan') }}</th>
  <th>{{ __('Status') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($tenants as $tenant)
    @php
      $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z0-9\p{Arabic}]/u', '', $tenant->name), 0, 2) ?: $tenant->slug);
      if (mb_strlen($initials) > 2) $initials = mb_substr($initials, 0, 2);
      $hasUsers = ($tenant->users_count ?? 0) > 0;
    @endphp
    <tr>
      <td>
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span>
          </span>
          <div>
            <span class="fw-medium d-block">{{ $tenant->name }}</span>
            <span class="text-muted small">{{ $tenant->slug }}</span>
          </div>
        </div>
      </td>
      <td><span class="text-nowrap">{{ $tenant->created_at?->format('Y-m-d') }}</span></td>
      <td>
        @if($tenant->plan)
          <span class="badge bg-label-primary">{{ __(ucfirst($tenant->plan)) }}</span>
        @else
          <span class="badge bg-label-secondary">—</span>
        @endif
      </td>
      <td>
        @if($hasUsers)
          <span class="badge bg-label-success">{{ __('Active') }}</span>
          <span class="text-muted small d-block mt-0">{{ __('with users') }}</span>
        @else
          <span class="badge bg-label-warning">{{ __('Pending') }}</span>
        @endif
      </td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('admin.core.tenants.show', $tenant) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
            <i class="icon-base ti tabler-eye"></i>
          </a>
          <a href="{{ route('admin.core.tenants.edit', $tenant) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          <form action="{{ route('admin.core.tenants.destroy', $tenant) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this tenant?') }}');">
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
      <td colspan="5" class="text-center text-muted py-5">
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
