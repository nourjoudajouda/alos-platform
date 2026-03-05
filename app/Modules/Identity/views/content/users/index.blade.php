@php
  $crudIndexId = 'users';
  $crudIndexTitle = __('Internal Users') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('identity.users.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Internal Users');
  $crudIndexAddUrl = route('identity.users.create');
  $crudIndexAddLabel = __('Add User');
  $crudIndexEmptyMessage = __('No users in this office yet.');
  $crudIndexEmptyLink = route('identity.users.create');
  $crudIndexEmptyLinkText = __('Add User');
  $crudIndexShowViewToggle = false;
  $items = $users;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Internal users are the staff inside your current office (tenant): Admin, Managing Partner, Lawyer, Assistant, Finance. You only see and manage users of your own office.') }}
@endsection

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Office Users'), 'value' => $totalUsers ?? 0, 'subtitle' => __('Same tenant'), 'icon' => 'ti tabler-users', 'bgLabel' => 'primary'])
@endsection

@section('crud_filters_hidden_inputs')
  @if(request('role'))<input type="hidden" name="role" value="{{ request('role') }}">@endif
@endsection

@section('crud_offcanvas')
  <form action="{{ route('identity.users.index') }}" method="get" id="usersFiltersFormSide">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div class="mb-3">
      <label for="filterRole" class="form-label">{{ __('Filter by Role') }}</label>
      <select name="role" id="filterRole" class="form-select">
        <option value="">{{ __('All') }}</option>
        @foreach($internalRoles ?? [] as $r)
          <option value="{{ $r->name }}" {{ ($filterRole ?? '') === $r->name ? 'selected' : '' }}>
            {{ __(\Illuminate\Support\Str::title(str_replace('_', ' ', $r->name))) }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Apply') }}</button>
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('Close') }}</button>
    </div>
  </form>
@endsection

@section('crud_table_header')
  <th>{{ __('Name') }}</th>
  <th>{{ __('Email') }}</th>
  <th>{{ __('Role') }}</th>
  <th class="text-nowrap" style="min-width: 5rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($users as $u)
    @php
      $roleName = $u->getRoleNames()->first();
      $roleDisplayName = $roleName ? __(\Illuminate\Support\Str::title(str_replace('_', ' ', $roleName))) : '—';
    @endphp
    <tr>
      <td>
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-sm flex-shrink-0">
            <img src="{{ $u->profile_photo_url }}" alt="" class="rounded-circle" width="32" height="32">
          </span>
          <span class="fw-medium">{{ $u->name }}</span>
        </div>
      </td>
      <td><span class="text-muted">{{ $u->email }}</span></td>
      <td><span class="badge bg-label-info">{{ $roleDisplayName }}</span></td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('identity.users.edit', $u) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
        </div>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="4" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-users icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection

@section('crud_extra_script')
<script>
(function() {
  var formSide = document.getElementById('usersFiltersFormSide');
  if (formSide && document.getElementById('filterRole')) {
    document.getElementById('filterRole').addEventListener('change', function() { formSide.submit(); });
  }
})();
</script>
@endsection
