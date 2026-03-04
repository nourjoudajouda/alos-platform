@php
  $configData = Helper::appClasses();
  $isRtl = ($configData['textDirection'] ?? 'ltr') === 'rtl';
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $totalRoles = $totalRoles ?? 0;
  $rolesWithPermissions = $rolesWithPermissions ?? 0;
  $rolesWithoutPermissions = $rolesWithoutPermissions ?? 0;
  $recentRoles = $recentRoles ?? 0;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Roles') . ' — ' . config('app.name'))

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
            <p class="card-title mb-1 text-muted small">{{ __('Total Roles') }}</p>
            <h4 class="mb-0 fw-bold">{{ $totalRoles }}</h4>
            <small class="text-muted">{{ __('Roles') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-shield icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('With Permissions') }}</p>
            <h4 class="mb-0 fw-bold">{{ $rolesWithPermissions }}</h4>
            <small class="text-muted">{{ __('Roles') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-success">
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
            <p class="card-title mb-1 text-muted small">{{ __('Last 30 days') }}</p>
            <h4 class="mb-0 fw-bold">{{ $recentRoles }}</h4>
            <small class="text-muted">{{ __('Roles') }}</small>
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
            <p class="card-title mb-1 text-muted small">{{ __('Without Permissions') }}</p>
            <h4 class="mb-0 fw-bold">{{ $rolesWithoutPermissions }}</h4>
            <small class="text-muted">{{ __('Roles') }}</small>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="icon-base ti tabler-shield-off icon-24px"></i>
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
      <form action="{{ route('core.roles.index') }}" method="get" id="filtersForm">
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
              <button type="button" class="btn btn-outline-primary btn-sm" id="rolesFiltersBtn" aria-controls="rolesFiltersOffcanvas">
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

  {{-- قائمة جانبية Filters (للأدوار يمكن إضافة فلتر لاحقاً) --}}
  <div class="offcanvas offcanvas-{{ $isRtl ? 'start' : 'end' }}" tabindex="-1" id="rolesFiltersOffcanvas" aria-labelledby="rolesFiltersOffcanvasLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="rolesFiltersOffcanvasLabel">{{ __('Filters') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      <form action="{{ route('core.roles.index') }}" method="get" id="filtersFormSide">
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

  {{-- بطاقة الجدول + Add Role في الهيدر — نفس تصميم Tenants --}}
  <div class="card crud-table">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0">{{ __('Roles') }}</h5>
      <a href="{{ route('core.roles.create') }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus icon-20px {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Add Role') }}
      </a>
    </div>
    <div class="table-responsive">
      <table class="table table-hover" dir="{{ $contentDir }}">
        <thead>
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Permissions') }}</th>
            <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($roles as $role)
            @php
              $roleDisplayName = __(\Illuminate\Support\Str::title(str_replace('_', ' ', $role->name)));
              $roleInitials = mb_substr(preg_replace('/\s+/', '', $roleDisplayName), 0, 2);
              if (mb_strlen($roleInitials) < 2) $roleInitials = strtoupper(mb_substr($role->name, 0, 2));
              $systemRoleNames = config('roles.system_role_names', ['admin']);
            @endphp
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <span class="avatar avatar-sm flex-shrink-0">
                    <span class="avatar-initial rounded bg-label-primary">{{ strtoupper($roleInitials) }}</span>
                  </span>
                  <div>
                    <span class="fw-medium d-block">{{ $roleDisplayName }}</span>
                    <span class="text-muted small">{{ $role->name }}</span>
                  </div>
                </div>
              </td>
              <td><span class="badge bg-label-info">{{ $role->permissions_count }}</span></td>
              <td class="text-nowrap">
                <div class="table-actions">
                  <a href="{{ route('core.roles.show', $role) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                    <i class="icon-base ti tabler-eye"></i>
                  </a>
                  <a href="{{ route('core.roles.edit', $role) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
                    <i class="icon-base ti tabler-pencil"></i>
                  </a>
                  @if (!in_array($role->name, $systemRoleNames, true))
                    <form action="{{ route('core.roles.destroy', $role) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this role?') }}');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted py-5">
                <i class="icon-base ti tabler-shield icon-32px d-block mb-2 opacity-50"></i>
                {{ __('No roles yet.') }} <a href="{{ route('core.roles.create') }}">{{ __('Add Role') }}</a>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($roles->hasPages())
      <div class="card-footer">
        {{ $roles->links() }}
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
      var btn = document.getElementById('rolesFiltersBtn');
      var ocEl = document.getElementById('rolesFiltersOffcanvas');
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
