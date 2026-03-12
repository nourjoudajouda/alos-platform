@php
  $reminderRulesRoutePrefix = $reminderRulesRoutePrefix ?? 'company.reminder-rules';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Reminder Rule') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add Reminder Rule') }}</h4>
    <a href="{{ route($reminderRulesRoutePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-right {{ app()->getLocale() === 'ar' ? 'ms-1' : 'me-1' }}"></i>
      {{ __('Back to list') }}
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      @include('core::content.reminder-rules._form', [
        'action' => route($reminderRulesRoutePrefix . '.store'),
        'method' => 'POST',
        'submitLabel' => __('Create Rule'),
      ])
    </div>
  </div>
</div>
@endsection
