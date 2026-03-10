@php
  $crudIndexId = 'consultations';
  $crudIndexTitle = __('Consultations') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.consultations.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Consultations');
  $crudIndexAddUrl = route('admin.core.consultations.create');
  $crudIndexAddLabel = __('Add Consultation');
  $crudIndexEmptyMessage = __('No consultations yet.');
  $crudIndexEmptyLink = route('admin.core.consultations.create');
  $crudIndexEmptyLinkText = __('Add Consultation');
  $crudIndexShowViewToggle = false;
  $items = $consultations;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\Consultation::STATUSES;
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Consultations are linked to clients. You only see consultations for clients you have team access to.') }}
@endsection

@section('crud_stats')
@endsection

@section('crud_filters_hidden_inputs')
  @if(request('client_id'))<input type="hidden" name="client_id" value="{{ request('client_id') }}">@endif
  @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
@endsection

@section('crud_offcanvas')
  <form action="{{ route('admin.core.consultations.index') }}" method="get" id="filtersFormSideConsultations">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <div class="mb-3">
      <label for="filterConsultationClientId" class="form-label">{{ __('Client') }}</label>
      <select name="client_id" id="filterConsultationClientId" class="form-select">
        <option value="">{{ __('All') }}</option>
        @foreach($clients as $c)
          <option value="{{ $c->id }}" {{ ($filterClientId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3">
      <label for="filterConsultationStatus" class="form-label">{{ __('Status') }}</label>
      <select name="status" id="filterConsultationStatus" class="form-select">
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
  <th>{{ __('Title') }}</th>
  <th>{{ __('Client') }}</th>
  <th>{{ __('Date') }}</th>
  <th>{{ __('Responsible') }}</th>
  <th>{{ __('Status') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($consultations as $consultation)
    <tr>
      <td><span class="fw-medium">{{ $consultation->title }}</span></td>
      <td>
        <a href="{{ route('admin.core.clients.show', [$consultation->client, 'tab' => 'consultations']) }}">{{ $consultation->client->name }}</a>
      </td>
      <td><span class="text-muted small">{{ $consultation->consultation_date?->format('Y-m-d') ?? '—' }}</span></td>
      <td><span class="text-muted small">{{ $consultation->responsibleUser?->name ?? '—' }}</span></td>
      <td>
        @php
          $statusClass = match($consultation->status) {
            'open' => 'primary',
            'completed' => 'success',
            'archived' => 'secondary',
            default => 'secondary',
          };
        @endphp
        <span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$consultation->status] ?? $consultation->status) }}</span>
        @if($consultation->is_shared_with_client)
          <span class="badge bg-label-info ms-1">{{ __('Shared') }}</span>
        @endif
      </td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('admin.core.consultations.show', $consultation) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
            <i class="icon-base ti tabler-eye"></i>
          </a>
          @can('consultations.manage')
          <a href="{{ route('admin.core.consultations.edit', $consultation) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          <form action="{{ route('admin.core.consultations.destroy', $consultation) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this consultation?') }}');">
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
        <i class="icon-base ti tabler-calendar-event icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection

@section('crud_extra_script')
<script>
(function() {
  var formSide = document.getElementById('filtersFormSideConsultations');
  if (formSide) {
    var el1 = document.getElementById('filterConsultationClientId');
    var el2 = document.getElementById('filterConsultationStatus');
    if (el1) el1.addEventListener('change', function() { formSide.submit(); });
    if (el2) el2.addEventListener('change', function() { formSide.submit(); });
  }
})();
</script>
@endsection
