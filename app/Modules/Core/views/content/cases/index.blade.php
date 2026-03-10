@php
  $crudIndexId = 'cases';
  $crudIndexTitle = __('Cases') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.cases.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Cases');
  $crudIndexAddUrl = route('admin.core.cases.create');
  $crudIndexAddLabel = __('Add Case');
  $crudIndexEmptyMessage = __('No cases yet.');
  $crudIndexEmptyLink = route('admin.core.cases.create');
  $crudIndexEmptyLinkText = __('Add Case');
  $crudIndexShowViewToggle = false;
  $items = $cases;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseModel::STATUSES;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Cases are linked to clients. You only see cases for clients you have team access to.') }}
@endsection

@section('crud_stats')
@endsection

@section('crud_filters_hidden_inputs')
  @if(request('client_id'))<input type="hidden" name="client_id" value="{{ request('client_id') }}">@endif
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
@endsection

@section('crud_offcanvas')
  <form action="{{ route('admin.core.cases.index') }}" method="get" id="filtersFormSideCases">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div class="mb-3">
      <label for="filterCaseClientId" class="form-label">{{ __('Client') }}</label>
      <select name="client_id" id="filterCaseClientId" class="form-select">
        <option value="">{{ __('All') }}</option>
        @foreach($clients as $c)
          <option value="{{ $c->id }}" {{ ($filterClientId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label for="filterCaseStatus" class="form-label">{{ __('Status') }}</label>
      <select name="status" id="filterCaseStatus" class="form-select">
        <option value="">{{ __('All') }}</option>
        @foreach($statusLabels as $val => $label)
          <option value="{{ $val }}" {{ ($filterStatus ?? '') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
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
  <th>{{ __('Case number') }}</th>
  <th>{{ __('Client') }}</th>
  <th>{{ __('Type') }}</th>
  <th>{{ __('Status') }}</th>
  <th>{{ __('Responsible lawyer') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($cases as $case)
    <tr>
      <td><span class="fw-medium">{{ $case->case_number }}</span></td>
      <td>
        <a href="{{ route('admin.core.clients.show', [$case->client, 'tab' => 'cases']) }}">{{ $case->client->name }}</a>
      </td>
      <td><span class="text-muted small">{{ $case->case_type ?? '—' }}</span></td>
      <td>
        @php
          $statusClass = match($case->status) {
            'open' => 'primary',
            'pending' => 'warning',
            'closed' => 'secondary',
            default => 'secondary',
          };
        @endphp
        <span class="badge bg-label-{{ $statusClass }}">{{ __(\App\Models\CaseModel::STATUSES[$case->status] ?? $case->status) }}</span>
      </td>
      <td><span class="text-muted small">{{ $case->responsibleLawyer?->name ?? '—' }}</span></td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('admin.core.cases.show', $case) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
            <i class="icon-base ti tabler-eye"></i>
          </a>
          @can('cases.manage')
          <a href="{{ route('admin.core.cases.edit', $case) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          <form action="{{ route('admin.core.cases.destroy', $case) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this case?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}">
              <i class="icon-base ti tabler-trash"></i>
            </button>
          </form>
          @endcan
        </div>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="6" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-briefcase icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection

@section('crud_extra_script')
<script>
(function() {
  var formSide = document.getElementById('filtersFormSideCases');
  if (formSide) {
    var el1 = document.getElementById('filterCaseClientId');
    var el2 = document.getElementById('filterCaseStatus');
    if (el1) el1.addEventListener('change', function() { formSide.submit(); });
    if (el2) el2.addEventListener('change', function() { formSide.submit(); });
  }
})();
</script>
@endsection
