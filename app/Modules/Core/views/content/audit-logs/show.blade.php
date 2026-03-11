@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Audit Log') . ' #' . $log->id . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('admin.core.audit-logs.index') }}">{{ __('Audit Log') }}</a></li>
          <li class="breadcrumb-item active">{{ __('Entry') }} #{{ $log->id }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ $log->action }}</h4>
      <p class="text-muted small mb-0">
        {{ $log->entity_type }} @if($log->entity_id)#{{ $log->entity_id }}@endif
        · {{ $log->created_at?->format('Y-m-d H:i:s') }}
        @if($log->tenant)
          · {{ $log->tenant->name }}
        @endif
      </p>
    </div>
    <a href="{{ route('admin.core.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">{{ __('Details') }}</h6>
        </div>
        <div class="card-body">
          <dl class="row mb-0 small">
            <dt class="col-sm-4">{{ __('ID') }}</dt>
            <dd class="col-sm-8">{{ $log->id }}</dd>
            <dt class="col-sm-4">{{ __('Action') }}</dt>
            <dd class="col-sm-8"><code>{{ $log->action }}</code></dd>
            <dt class="col-sm-4">{{ __('Entity type') }}</dt>
            <dd class="col-sm-8">{{ $log->entity_type }}</dd>
            <dt class="col-sm-4">{{ __('Entity ID') }}</dt>
            <dd class="col-sm-8">{{ $log->entity_id }}</dd>
            <dt class="col-sm-4">{{ __('Tenant') }}</dt>
            <dd class="col-sm-8">{{ $log->tenant?->name ?? '—' }}</dd>
            <dt class="col-sm-4">{{ __('User') }}</dt>
            <dd class="col-sm-8">{{ $log->user?->name ?? $log->user_id ?? '—' }}</dd>
            <dt class="col-sm-4">{{ __('IP address') }}</dt>
            <dd class="col-sm-8">{{ $log->ip_address }}</dd>
            <dt class="col-sm-4">{{ __('Created at') }}</dt>
            <dd class="col-sm-8">{{ $log->created_at?->format('Y-m-d H:i:s') }}</dd>
          </dl>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">{{ __('Old values') }}</h6>
        </div>
        <div class="card-body">
          <pre class="mb-0 small bg-light p-3 rounded" style="max-height: 250px; overflow: auto;">{{ json_encode($log->old_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">{{ __('New values') }}</h6>
        </div>
        <div class="card-body">
          <pre class="mb-0 small bg-light p-3 rounded" style="max-height: 250px; overflow: auto;">{{ json_encode($log->new_values ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}' }}</pre>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
