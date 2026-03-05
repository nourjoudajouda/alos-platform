@php
  $crudIndexId = 'clients';
  $crudIndexTitle = __('Clients') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('core.clients.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Clients');
  $crudIndexAddUrl = route('core.clients.create');
  $crudIndexAddLabel = __('Add Client');
  $crudIndexEmptyMessage = __('No clients yet.');
  $crudIndexEmptyLink = route('core.clients.create');
  $crudIndexEmptyLinkText = __('Add Client');
  $crudIndexShowViewToggle = true;
  $items = $clients;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $totalClients = $totalClients ?? 0;
  $recentClients = $recentClients ?? 0;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Clients are the parties (individuals or entities) that the office represents. Here you manage all clients. Team access can be configured per client.') }}
@endsection

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Clients'), 'value' => $totalClients, 'subtitle' => __('Clients'), 'icon' => 'ti tabler-users-group', 'bgLabel' => 'primary'])
  @include('core::_partials.crud-stat-card', ['title' => __('Last 30 days'), 'value' => $recentClients, 'subtitle' => __('New'), 'icon' => 'ti tabler-calendar', 'bgLabel' => 'info'])
@endsection

@section('crud_filters_hidden_inputs')
  @if(request('tenant_id'))<input type="hidden" name="tenant_id" value="{{ request('tenant_id') }}">@endif
@endsection

@section('crud_offcanvas')
  <form action="{{ route('core.clients.index') }}" method="get" id="filtersFormSideClients">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    @if(request('view'))<input type="hidden" name="view" value="{{ request('view') }}">@endif
    <div class="mb-3">
      <label for="filterTenantId" class="form-label">{{ __('Tenant') }}</label>
      <select name="tenant_id" id="filterTenantId" class="form-select">
        <option value="">{{ __('All') }}</option>
        @foreach($tenants as $t)
          <option value="{{ $t->id }}" {{ ($filterTenantId ?? '') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
        @endforeach
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
  <th>{{ __('Email') }}</th>
  <th>{{ __('Tenant') }}</th>
  <th>{{ __('Created At') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($clients as $client)
    @php
      $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z0-9\p{Arabic}]/u', '', $client->name), 0, 2) ?: 'CL');
      if (mb_strlen($initials) > 2) $initials = mb_substr($initials, 0, 2);
    @endphp
    <tr>
      <td>
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-sm flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span>
          </span>
          <span class="fw-medium">{{ $client->name }}</span>
        </div>
      </td>
      <td><span class="text-muted small">{{ $client->email ?? '—' }}</span></td>
      <td>
        @if($client->tenant)
          <span class="badge bg-label-secondary">{{ $client->tenant->name }}</span>
        @else
          <span class="text-muted">—</span>
        @endif
      </td>
      <td><span class="text-nowrap">{{ $client->created_at?->format('Y-m-d') }}</span></td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('core.clients.show', $client) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
            <i class="icon-base ti tabler-eye"></i>
          </a>
          <a href="{{ route('core.clients.edit', $client) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          <form action="{{ route('core.clients.destroy', $client) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this client?') }}');">
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
        <i class="icon-base ti tabler-users-group icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection

@section('crud_extra_script')
<script>
(function() {
  var formSide = document.getElementById('filtersFormSideClients');
  if (formSide) {
    var el = document.getElementById('filterTenantId');
    if (el) el.addEventListener('change', function() { formSide.submit(); });
  }
})();
</script>
@endsection
