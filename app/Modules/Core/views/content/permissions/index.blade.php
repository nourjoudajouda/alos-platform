@php
  $configData = Helper::appClasses();
  $isRtl = ($configData['textDirection'] ?? 'ltr') === 'rtl';
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $totalPermissions = $totalPermissions ?? 0;
  $viewPermissions = $viewPermissions ?? 0;
  $managePermissions = ($createPermissions ?? 0) + ($editPermissions ?? 0) + ($deletePermissions ?? 0);
  $recentPermissions = $recentPermissions ?? 0;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Permissions') . ' — ' . config('app.name'))

@section('page-style')
@include('core::_partials.crud-table-styles')
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  {{-- إحصائيات علوية (4 كروت) — نفس تصميم Tenants --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Total Permissions') }}</p>
            <h4 class="mb-0 fw-bold">{{ $totalPermissions }}</h4>
            <small class="text-muted">{{ __('Permissions') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-key icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('View-only permissions') }}</p>
            <h4 class="mb-0 fw-bold">{{ $viewPermissions }}</h4>
            <small class="text-muted">{{ __('Names start with: view …') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base ti tabler-eye icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Create / Edit / Delete permissions') }}</p>
            <h4 class="mb-0 fw-bold">{{ $managePermissions }}</h4>
            <small class="text-muted">{{ __('Names start with: create, edit or delete') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base ti tabler-edit icon-24px"></i>
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
            <h4 class="mb-0 fw-bold">{{ $recentPermissions }}</h4>
            <small class="text-muted">{{ __('Permissions') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="icon-base ti tabler-calendar icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- بطاقة Filters — نفس تصميم Tenants --}}
  <div class="card mb-4">
    <div class="card-header py-3">
      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('core.permissions.index') }}" method="get" id="filtersForm">
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
              <button type="button" class="btn btn-outline-primary btn-sm" id="permissionsFiltersBtn" aria-controls="permissionsFiltersOffcanvas">
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

  {{-- قائمة جانبية Filters --}}
  <div class="offcanvas offcanvas-{{ $isRtl ? 'start' : 'end' }}" tabindex="-1" id="permissionsFiltersOffcanvas" aria-labelledby="permissionsFiltersOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="permissionsFiltersOffcanvasLabel">{{ __('Filters') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      <form action="{{ route('core.permissions.index') }}" method="get" id="filtersFormSide">
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <input type="hidden" name="search" value="{{ request('search') }}">
        @if(request('view'))<input type="hidden" name="view" value="{{ request('view') }}">@endif
        <p class="text-muted small">{{ __('Use search and per page in the main filters.') }}</p>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Apply') }}</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}">{{ __('Close') }}</button>
        </div>
      </form>
    </div>
  </div>

  {{-- بطاقة الجدول + Add Permission في الهيدر — نفس تصميم Tenants --}}
  <div class="card crud-table">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0">{{ __('Permissions') }}</h5>
      <a href="{{ route('core.permissions.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus icon-20px {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Add Permission') }}
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover" dir="{{ $contentDir }}">
        <thead>
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Guard') }}</th>
            <th>{{ __('Created At') }}</th>
            <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($permissions as $permission)
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <span class="avatar avatar-sm flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-secondary">
                      <i class="icon-base ti tabler-key small"></i>
                    </span>
                  </span>
                  <div>
                    <code class="small">{{ $permission->name }}</code>
                  </div>
                </div>
              </td>
              <td><span class="text-muted small">{{ $permission->guard_name }}</span></td>
              <td><span class="text-nowrap">{{ $permission->created_at?->format('Y-m-d') }}</span></td>
              <td class="text-nowrap">
                <div class="table-actions">
                  <a href="{{ route('core.permissions.show', $permission) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                    <i class="icon-base ti tabler-eye"></i>
                  </a>
                  <a href="{{ route('core.permissions.edit', $permission) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
                    <i class="icon-base ti tabler-pencil"></i>
                  </a>
                  <form action="{{ route('core.permissions.destroy', $permission) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this permission?') }}');">
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
              <td colspan="4" class="text-center text-muted py-5">
                <i class="icon-base ti tabler-key icon-32px d-block mb-2 opacity-50"></i>
                {{ __('No permissions yet.') }} <a href="{{ route('core.permissions.create') }}">{{ __('Add Permission') }}</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($permissions->hasPages())
      <div class="card-footer">
        {{ $permissions->links() }}
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
      var btn = document.getElementById('permissionsFiltersBtn');
      var ocEl = document.getElementById('permissionsFiltersOffcanvas');
      if (btn && ocEl && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
        btn.addEventListener('click', function() {
          bootstrap.Offcanvas.getOrCreateInstance(ocEl).show();
        });
      }
      document.getElementById('perPageSelect')?.addEventListener('change', function() {
        document.getElementById('filtersForm')?.submit();
      });
    }
    if (document.readyState === 'complete') { init(); } else { window.addEventListener('load', init); }
  })();
</script>
@endsection
