@php
  $crudIndexId = 'roles';
  $crudIndexTitle = __('Roles') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('core.roles.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Roles');
  $crudIndexAddUrl = route('core.roles.create');
  $crudIndexAddLabel = __('Add Role');
  $crudIndexEmptyMessage = __('No roles yet.');
  $crudIndexEmptyLink = route('core.roles.create');
  $crudIndexEmptyLinkText = __('Add Role');
  $crudIndexShowViewToggle = true;
  $items = $roles;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Roles'), 'value' => $totalRoles ?? 0, 'subtitle' => __('Roles'), 'icon' => 'ti tabler-shield', 'bgLabel' => 'primary'])
  @include('core::_partials.crud-stat-card', ['title' => __('With Permissions'), 'value' => $rolesWithPermissions ?? 0, 'subtitle' => __('Roles'), 'icon' => 'ti tabler-key', 'bgLabel' => 'success'])
  @include('core::_partials.crud-stat-card', ['title' => __('Last 30 days'), 'value' => $recentRoles ?? 0, 'subtitle' => __('Roles'), 'icon' => 'ti tabler-calendar', 'bgLabel' => 'info'])
  @include('core::_partials.crud-stat-card', ['title' => __('Without Permissions'), 'value' => $rolesWithoutPermissions ?? 0, 'subtitle' => __('Roles'), 'icon' => 'ti tabler-shield-off', 'bgLabel' => 'secondary'])
@endsection

@section('crud_offcanvas')
  <form action="{{ route('core.roles.index') }}" method="get" id="rolesFiltersFormSide">
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
  <th>{{ __('Permissions') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
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
          <a href="{{ route('core.roles.show', $role) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}"><i class="icon-base ti tabler-eye"></i></a>
          <a href="{{ route('core.roles.edit', $role) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}"><i class="icon-base ti tabler-pencil"></i></a>
          @if (!in_array($role->name, $systemRoleNames, true))
            <form action="{{ route('core.roles.destroy', $role) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this role?') }}');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}"><i class="icon-base ti tabler-trash"></i></button>
            </form>
          @endif
        </div>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="3" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-shield icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection
