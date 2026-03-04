@php
  $configData = Helper::appClasses();
  $isRtl = ($configData['textDirection'] ?? 'ltr') === 'rtl';
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $totalTenants = $totalTenants ?? 0;
  $tenantsWithUsers = $tenantsWithUsers ?? 0;
  $recentTenants = $recentTenants ?? 0;
  $withoutUsers = $totalTenants - $tenantsWithUsers;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Tenants') . ' — ' . config('app.name'))

@section('page-style')
<style>
  .tenants-table .table { direction: inherit; }
  .tenants-table .table-actions {
    display: inline-flex;
    flex-wrap: nowrap;
    align-items: center;
    gap: 0.25rem;
    white-space: nowrap;
  }
  .tenants-table .table-actions .btn-icon,
  .tenants-table .table-actions form { flex-shrink: 0; }
  .tenants-table .table-actions .btn-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 0.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }
  .tenants-table .table-actions .btn-icon .icon-base { font-size: 1.25rem; }
  /* ألوان الأيقونات: عرض = أزرق، تعديل = برتقالي، حذف = أحمر */
  .tenants-table .table-actions .btn-text-primary,
  .tenants-table .table-actions .btn-text-primary .icon-base { color: var(--bs-primary) !important; }
  .tenants-table .table-actions .btn-text-primary:hover { background: rgba(var(--bs-primary-rgb), 0.08); color: var(--bs-primary) !important; }
  .tenants-table .table-actions .btn-text-primary:hover .icon-base { color: var(--bs-primary) !important; }
  .tenants-table .table-actions .btn-text-warning,
  .tenants-table .table-actions .btn-text-warning .icon-base { color: var(--bs-warning) !important; }
  .tenants-table .table-actions .btn-text-warning:hover { background: rgba(var(--bs-warning-rgb), 0.08); color: var(--bs-warning) !important; }
  .tenants-table .table-actions .btn-text-warning:hover .icon-base { color: var(--bs-warning) !important; }
  .tenants-table .table-actions .btn-text-danger,
  .tenants-table .table-actions .btn-text-danger .icon-base { color: var(--bs-danger) !important; }
  .tenants-table .table-actions .btn-text-danger:hover { background: rgba(var(--bs-danger-rgb), 0.08); color: var(--bs-danger) !important; }
  .tenants-table .table-actions .btn-text-danger:hover .icon-base { color: var(--bs-danger) !important; }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  {{-- Vuexy User List style: إحصائيات علوية (4 كروت) --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Total Tenants') }}</p>
            <h4 class="mb-0 fw-bold">{{ $totalTenants }}</h4>
            <small class="text-muted">{{ __('Tenants') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-building-store icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('With Users') }}</p>
            <h4 class="mb-0 fw-bold">{{ $tenantsWithUsers }}</h4>
            <small class="text-muted">{{ __('Tenants') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base ti tabler-users icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Last 30 days') }}</p>
            <h4 class="mb-0 fw-bold">{{ $recentTenants }}</h4>
            <small class="text-muted">{{ __('Tenants') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base ti tabler-calendar icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Without Users') }}</p>
            <h4 class="mb-0 fw-bold">{{ $withoutUsers }}</h4>
            <small class="text-muted">{{ __('Tenants') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="icon-base ti tabler-user-off icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- بطاقة Filters — Per Page + زر فلتر (يفتح قائمة جانبية فيها Plan و Status فقط) + Search + عرض --}}
  <div class="card mb-4">
    <div class="card-header py-3">
      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('core.tenants.index') }}" method="get" id="filtersForm">
        @if(request('plan'))<input type="hidden" name="plan" value="{{ request('plan') }}">@endif
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        <div class="d-flex flex-column flex-md-row flex-wrap align-items-stretch align-items-md-end gap-3">
          <div class="d-flex flex-wrap align-items-end gap-3">
            <div class="filter-field">
              <label for="perPageSelect" class="form-label small text-muted mb-1">{{ __('Per Page') }}</label>
              <select name="per_page" id="perPageSelect" class="form-select form-select-sm" style="width: 5rem;">
                @foreach([10, 25, 50, 100] as $n)
                  <option value="{{ $n }}" {{ (int) $perPage === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
              </select>
            </div>
            <div class="filter-field">
              <label class="form-label small text-muted mb-1 d-block">{{ __('Filters') }}</label>
              <button type="button" class="btn btn-outline-primary btn-sm" id="tenantsFiltersBtn" aria-controls="tenantsFiltersOffcanvas">
                <i class="icon-base ti tabler-filter {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
                {{ __('Filters') }}
              </button>
            </div>
          </div>
          <div class="vr d-none d-md-block opacity-25 flex-shrink-0" style="align-self: stretch;"></div>
          <div class="d-flex flex-wrap align-items-end gap-2 {{ $isRtl ? 'me-auto' : 'ms-md-auto' }}">
            <div class="input-group input-group-merge" style="width: 12rem;">
              <input type="search" name="search" class="form-control form-control-sm" placeholder="{{ __('Search placeholder') }}" value="{{ request('search') }}" aria-label="{{ __('Search') }}">
              <button type="submit" class="btn btn-primary btn-sm" aria-label="{{ __('Search') }}">
                <i class="icon-base ti tabler-search"></i>
              </button>
            </div>
            <div class="btn-group btn-group-sm" role="group">
              @php $currentView = request('view', 'list'); @endphp
              <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="btn {{ $currentView === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }} btn-icon" title="{{ __('Grid view') }}">
                <i class="icon-base ti tabler-layout-grid"></i>
              </a>
              <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="btn {{ $currentView === 'list' ? 'btn-primary' : 'btn-outline-secondary' }} btn-icon" title="{{ __('List view') }}">
                <i class="icon-base ti tabler-list"></i>
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- قائمة جانبية — فيها Plan و Status فقط --}}
  <div class="offcanvas offcanvas-{{ $isRtl ? 'start' : 'end' }}" tabindex="-1" id="tenantsFiltersOffcanvas" aria-labelledby="tenantsFiltersOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="tenantsFiltersOffcanvasLabel">{{ __('Filters') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      <form action="{{ route('core.tenants.index') }}" method="get" id="filtersFormSide">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        @if(request('view'))<input type="hidden" name="view" value="{{ request('view') }}">@endif
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
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}">{{ __('Close') }}</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Vuexy: بطاقة الجدول + Add Tenant في الهيدر --}}
  <div class="card tenants-table">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0">{{ __('Tenants') }}</h5>
      <a href="{{ route('core.tenants.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus icon-20px {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Add Tenant') }}
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover" dir="{{ $contentDir }}">
        <thead>
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Created At') }}</th>
            <th>{{ __('Plan') }}</th>
            <th>{{ __('Status') }}</th>
            <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
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
                  <a href="{{ route('core.tenants.show', $tenant) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                    <i class="icon-base ti tabler-eye"></i>
                  </a>
                  <a href="{{ route('core.tenants.edit', $tenant) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
                    <i class="icon-base ti tabler-pencil"></i>
                  </a>
                  <form action="{{ route('core.tenants.destroy', $tenant) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this tenant?') }}');">
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
                {{ __('No tenants yet.') }} <a href="{{ route('core.tenants.create') }}">{{ __('Add Tenant') }}</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($tenants->hasPages())
      <div class="card-footer">
        {{ $tenants->links() }}
      </div>
    @endif
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible mt-3" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
</div>

<script>
  (function() {
    function init() {
      var btn = document.getElementById('tenantsFiltersBtn');
      var ocEl = document.getElementById('tenantsFiltersOffcanvas');
      if (btn && ocEl && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
        btn.addEventListener('click', function() {
          bootstrap.Offcanvas.getOrCreateInstance(ocEl).show();
        });
      }
      document.getElementById('perPageSelect')?.addEventListener('change', function() {
        document.getElementById('filtersForm')?.submit();
      });
      document.querySelectorAll('#filterPlan, #filterStatus').forEach(function(el) {
        el.addEventListener('change', function() { document.getElementById('filtersFormSide')?.submit(); });
      });
    }
    if (document.readyState === 'complete') { init(); } else { window.addEventListener('load', init); }
  })();
</script>
@endsection
