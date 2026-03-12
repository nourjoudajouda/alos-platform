@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Contracts') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-0">{{ __('Contracts') }}</h4>
      <p class="text-body mb-0 small">{{ __('Contract dates per law firm. Edit contract dates from Law Firms.') }}</p>
    </div>
    <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-primary btn-sm">
      <i class="icon-base ti tabler-building-store me-1"></i>
      {{ __('Law Firms') }}
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.core.contracts.index') }}" method="get" class="d-flex flex-wrap gap-2 mb-3">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="text" name="search" class="form-control form-control-sm" style="max-width: 200px;" placeholder="{{ __('Search by name or slug') }}" value="{{ request('search') }}">
        @if($hasContractEndDate ?? true)
        <select name="expiring" class="form-select form-select-sm" style="max-width: 180px;">
          <option value="">{{ __('All contracts') }}</option>
          <option value="1" {{ request('expiring') === '1' ? 'selected' : '' }}>{{ __('Expiring in 30 days') }}</option>
        </select>
        @endif
        <button type="submit" class="btn btn-sm btn-primary">{{ __('Search') }}</button>
      </form>

      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>{{ __('Law Firm') }}</th>
              <th>{{ __('Contract start') }}</th>
              <th>{{ __('Contract end') }}</th>
              <th>{{ __('Plan') }}</th>
              <th>{{ __('Status') }}</th>
              <th class="text-nowrap" style="min-width: 6rem;">{{ __('Actions') }}</th>
            </tr>
          </thead>
          <tbody>
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
                  @if($tenant->is_active)
                    <span class="badge bg-label-success">{{ __('Active') }}</span>
                  @else
                    <span class="badge bg-label-secondary">{{ __('Suspended') }}</span>
                  @endif
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
                  {{ __('No law firms found.') }}
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($tenants->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">{{ __('Showing :from–:to of :total', ['from' => $tenants->firstItem(), 'to' => $tenants->lastItem(), 'total' => $tenants->total()]) }}</small>
          {{ $tenants->withQueryString()->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
