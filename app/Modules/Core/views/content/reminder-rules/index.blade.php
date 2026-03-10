@php
  $crudIndexId = 'reminder-rules';
  $crudIndexTitle = __('Reminder Rules') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.reminder-rules.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Session Reminder Rules');
  $crudIndexAddUrl = route('admin.core.reminder-rules.create');
  $crudIndexAddLabel = __('Add Rule');
  $crudIndexEmptyMessage = __('No reminder rules yet.');
  $crudIndexEmptyLink = route('admin.core.reminder-rules.create');
  $crudIndexEmptyLinkText = __('Add Rule');
  $crudIndexShowViewToggle = false;
  $items = $rules;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Configure when and how session reminders are sent to lawyers, team members, and optionally clients.') }}
@endsection

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Rules'), 'value' => $totalCount ?? 0, 'subtitle' => __('Rules'), 'icon' => 'ti tabler-bell', 'bgLabel' => 'primary'])
  @include('core::_partials.crud-stat-card', ['title' => __('Active'), 'value' => $activeCount ?? 0, 'subtitle' => __('Rules'), 'icon' => 'ti tabler-bell-check', 'bgLabel' => 'success'])
@endsection

@section('crud_filters_hidden_inputs')
@endsection

@section('crud_offcanvas')
  <p class="text-muted small">{{ __('Use per page in the main filters.') }}</p>
  <div class="d-flex gap-2">
    <a href="{{ route('admin.core.reminder-rules.index') }}" class="btn btn-outline-secondary flex-grow-1">{{ __('Reset') }}</a>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">{{ __('Close') }}</button>
  </div>
@endsection

@section('crud_table_header')
  <th>{{ __('Label') }}</th>
  <th>{{ __('Trigger') }}</th>
  <th>{{ __('Channels') }}</th>
  <th>{{ __('Notify Client') }}</th>
  <th>{{ __('Status') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($rules as $rule)
    @php
      $triggerDisplay = $rule->trigger_minutes >= 1440
        ? round($rule->trigger_minutes / 1440) . ' ' . __('days')
        : ($rule->trigger_minutes >= 60
            ? round($rule->trigger_minutes / 60) . ' ' . __('hours')
            : $rule->trigger_minutes . ' ' . __('min'));
      $channels = [];
      if ($rule->channel_database) $channels[] = __('In-app');
      if ($rule->channel_mail) $channels[] = __('Email');
    @endphp
    <tr>
      <td class="fw-medium">{{ $rule->label }}</td>
      <td>
        <span class="text-muted">{{ $triggerDisplay }}</span>
        <span class="d-block small">({{ $rule->trigger_minutes }} {{ __('min') }})</span>
      </td>
      <td>
        @forelse($channels as $ch)
          <span class="badge bg-label-info me-1">{{ $ch }}</span>
        @empty
          <span class="text-muted">—</span>
        @endforelse
      </td>
      <td>
        @if($rule->notify_client)
          <span class="badge bg-label-success">{{ __('Yes') }}</span>
        @else
          <span class="badge bg-label-secondary">{{ __('No') }}</span>
        @endif
      </td>
      <td>
        @if($rule->active)
          <span class="badge bg-label-success">{{ __('Active') }}</span>
        @else
          <span class="badge bg-label-secondary">{{ __('Inactive') }}</span>
        @endif
      </td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('admin.core.reminder-rules.edit', $rule) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          <form action="{{ route('admin.core.reminder-rules.destroy', $rule) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this reminder rule?') }}');">
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
      <td colspan="6" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-bell icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection
