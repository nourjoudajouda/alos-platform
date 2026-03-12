@php
  $crudIndexId = 'contracts';
  $crudIndexTitle = __('Contracts') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.contracts.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Contracts');
  $crudIndexAddUrl = route('admin.core.tenants.index');
  $crudIndexAddLabel = __('Law Firms');
  $crudIndexEmptyMessage = __('No law firms found.');
  $crudIndexEmptyLink = route('admin.core.tenants.index');
  $crudIndexEmptyLinkText = __('Law Firms');
  $crudIndexShowViewToggle = false;
  $items = $tenants;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $filterExpiring = request('expiring', '');
  $filterExpired = request('expired', '');
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Contract dates per law firm. Edit contract dates from Law Firms.') }}
@endsection

@section('crud_stats')
@endsection

@section('crud_filters_hidden_inputs')
  @if(request('expiring'))<input type="hidden" name="expiring" value="{{ request('expiring') }}">@endif
  @if(request('expired'))<input type="hidden" name="expired" value="{{ request('expired') }}">@endif
@endsection

@section('crud_offcanvas')
  <form action="{{ route('admin.core.contracts.index') }}" method="get" id="filtersFormContracts">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    @if($hasContractEndDate ?? true)
    <div class="mb-3">
      <label for="filterExpiring" class="form-label">{{ __('Filter by expiration') }}</label>
      <select name="expiring" id="filterExpiring" class="form-select">
        <option value="">{{ __('All contracts') }}</option>
        <option value="1" {{ $filterExpiring === '1' ? 'selected' : '' }}>{{ __('Expiring in 30 days') }}</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="filterExpired" class="form-label">{{ __('Filter by status') }}</label>
      <select name="expired" id="filterExpired" class="form-select">
        <option value="">{{ __('All') }}</option>
        <option value="1" {{ $filterExpired === '1' ? 'selected' : '' }}>{{ __('Expired only') }}</option>
      </select>
    </div>
    @endif
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Search') }}</button>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('Close') }}</button>
    </div>
  </form>
@endsection

@section('crud_table_header')
  <th>{{ __('Law Firm') }}</th>
  <th>{{ __('Contract start') }}</th>
  <th>{{ __('Contract end') }}</th>
  <th>{{ __('Plan') }}</th>
  <th>{{ __('Status') }}</th>
  <th class="text-nowrap" style="min-width: 6rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($tenants as $tenant)
    @php
      $end = $tenant->contract_end_date ?? $tenant->end_date;
      $isExpiring = $end && $end->isFuture() && $end->lte(now()->addDays(30));
    @endphp
    <tr>
      <td>
        <span class="fw-medium">{{ $tenant->name }}</span>
        <span class="text-muted small d-block">{{ $tenant->slug }}</span>
      </td>
      <td class="text-nowrap">{{ ($tenant->contract_start_date ?? $tenant->start_date)?->format('Y-m-d') ?? '—' }}</td>
      <td class="text-nowrap">
        @if($end)
          <span class="{{ $isExpiring ? 'text-warning fw-medium' : '' }}">{{ $end->format('Y-m-d') }}</span>
          @if($isExpiring)
            <span class="badge bg-label-warning ms-1">{{ __('Expiring soon') }}</span>
          @endif
        @else
          —
        @endif
      </td>
      <td>
        @if($tenant->subscriptionPlan)
          <span class="badge bg-label-primary">{{ $tenant->subscriptionPlan->plan_name }}</span>
        @else
          <span class="text-muted">—</span>
        @endif
      </td>
      <td>
        @php $subStatus = $tenant->subscription_status ?? $tenant->status ?? 'active'; @endphp
        <span class="badge bg-{{ $subStatus === 'expired' ? 'danger' : ($subStatus === 'active' || $subStatus === 'trial' ? 'success' : 'warning') }}">
          {{ ucfirst(__($subStatus)) }}
        </span>
      </td>
      <td>
        <a href="{{ route('admin.core.tenants.edit', $tenant) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('Edit') }}">
          <i class="icon-base ti tabler-pencil"></i>
        </a>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="6" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-file-contract icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection

@section('crud_extra_script')
<script>
(function() {
  var formSide = document.getElementById('filtersFormContracts');
  if (formSide) {
    document.querySelectorAll('#filterExpiring, #filterExpired').forEach(function(el) {
      if (el) el.addEventListener('change', function() { formSide.submit(); });
    });
  }
})();
</script>
@endsection
