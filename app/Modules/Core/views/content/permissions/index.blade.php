@php
  $crudIndexId = 'permissions';
  $crudIndexTitle = __('Permissions') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.permissions.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Permissions');
  $crudIndexAddUrl = route('admin.core.permissions.create');
  $crudIndexAddLabel = __('Add Permission');
  $crudIndexEmptyMessage = __('No permissions yet.');
  $crudIndexEmptyLink = route('admin.core.permissions.create');
  $crudIndexEmptyLinkText = __('Add Permission');
  $crudIndexShowViewToggle = true;
  $items = $permissions;
  $managePermissions = ($createPermissions ?? 0) + ($editPermissions ?? 0) + ($deletePermissions ?? 0);
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Permissions'), 'value' => $totalPermissions ?? 0, 'subtitle' => __('Permissions'), 'icon' => 'ti tabler-key', 'bgLabel' => 'primary'])
  @include('core::_partials.crud-stat-card', ['title' => __('View-only permissions'), 'value' => $viewPermissions ?? 0, 'subtitle' => __('Names start with: view …'), 'icon' => 'ti tabler-eye', 'bgLabel' => 'success'])
  @include('core::_partials.crud-stat-card', ['title' => __('Create / Edit / Delete permissions'), 'value' => $managePermissions, 'subtitle' => __('Names start with: create, edit or delete'), 'icon' => 'ti tabler-edit', 'bgLabel' => 'info'])
  @include('core::_partials.crud-stat-card', ['title' => __('Last 30 days'), 'value' => $recentPermissions ?? 0, 'subtitle' => __('Permissions'), 'icon' => 'ti tabler-calendar', 'bgLabel' => 'secondary'])
@endsection

@section('crud_offcanvas')
  <form action="{{ route('admin.core.permissions.index') }}" method="get" id="permissionsFiltersFormSide">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    @if(request('view'))<input type="hidden" name="view" value="{{ request('view') }}">@endif
    <p class="text-muted small">{{ __('Use search and per page in the main filters.') }}</p>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Apply') }}</button>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('Close') }}</button>
    </div>
  </form>
@endsection

@section('crud_table_header')
  <th>{{ __('Name') }}</th>
  <th>{{ __('Guard') }}</th>
  <th>{{ __('Created At') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($permissions as $permission)
    <tr>
      <td>
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary"><i class="icon-base ti tabler-key small"></i></span>
          </span>
          <code class="small">{{ $permission->name }}</code>
        </div>
      </td>
      <td><span class="text-muted small">{{ $permission->guard_name }}</span></td>
      <td><span class="text-nowrap">{{ $permission->created_at?->format('Y-m-d') }}</span></td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('admin.core.permissions.show', $permission) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}"><i class="icon-base ti tabler-eye"></i></a>
          <a href="{{ route('admin.core.permissions.edit', $permission) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}"><i class="icon-base ti tabler-pencil"></i></a>
          <form action="{{ route('admin.core.permissions.destroy', $permission) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this permission?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}"><i class="icon-base ti tabler-trash"></i></button>
          </form>
        </div>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="4" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-key icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection
