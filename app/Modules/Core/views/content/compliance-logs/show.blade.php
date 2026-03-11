@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Compliance Log') . ' #' . $log->id . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('admin.core.compliance-logs.index') }}">{{ __('Compliance Log') }}</a></li>
          <li class="breadcrumb-item active">{{ __('Entry') }} #{{ $log->id }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ $log->attempted_action }}</h4>
      <p class="text-muted small mb-0">
        {{ $log->target_entity }} @if($log->target_id)#{{ $log->target_id }}@endif
        · {{ $log->created_at?->format('Y-m-d H:i:s') }}
        @if($log->tenant)
          · {{ $log->tenant->name }}
        @endif
      </p>
    </div>
    <a href="{{ route('admin.core.compliance-logs.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
  </div>

  <div class="card">
    <div class="card-header">
      <h6 class="card-title mb-0">{{ __('Details') }}</h6>
    </div>
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">{{ __('ID') }}</dt>
        <dd class="col-sm-9">{{ $log->id }}</dd>
        <dt class="col-sm-3">{{ __('Attempted action') }}</dt>
        <dd class="col-sm-9"><code>{{ $log->attempted_action }}</code></dd>
        <dt class="col-sm-3">{{ __('Target entity') }}</dt>
        <dd class="col-sm-9">{{ $log->target_entity ?? '—' }}</dd>
        <dt class="col-sm-3">{{ __('Target ID') }}</dt>
        <dd class="col-sm-9">{{ $log->target_id ?? '—' }}</dd>
        <dt class="col-sm-3">{{ __('Description') }}</dt>
        <dd class="col-sm-9">{{ $log->description }}</dd>
        <dt class="col-sm-3">{{ __('Tenant') }}</dt>
        <dd class="col-sm-9">{{ $log->tenant?->name ?? '—' }}</dd>
        <dt class="col-sm-3">{{ __('User') }}</dt>
        <dd class="col-sm-9">{{ $log->user?->name ?? $log->user_id ?? '—' }}</dd>
        <dt class="col-sm-3">{{ __('User type') }}</dt>
        <dd class="col-sm-9">{{ $log->user_type ?? '—' }}</dd>
        <dt class="col-sm-3">{{ __('IP address') }}</dt>
        <dd class="col-sm-9">{{ $log->ip_address ?? '—' }}</dd>
        <dt class="col-sm-3">{{ __('Created at') }}</dt>
        <dd class="col-sm-9">{{ $log->created_at?->format('Y-m-d H:i:s') }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
