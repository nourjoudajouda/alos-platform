@php
  $configData = Helper::appClasses();
  $roleDisplayName = __(\Illuminate\Support\Str::title(str_replace('_', ' ', $role->name)));
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $roleDisplayName . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Role') }}: {{ $roleDisplayName }}</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('core.roles.edit', $role) }}" class="btn btn-warning btn-sm">{{ __('Edit') }}</a>
      <a href="{{ route('core.roles.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">{{ __('Name') }}</dt>
        <dd class="col-sm-9"><span class="badge bg-label-primary">{{ $roleDisplayName }}</span> <span class="text-muted small">({{ $role->name }})</span></dd>
        <dt class="col-sm-3">{{ __('Guard') }}</dt>
        <dd class="col-sm-9">{{ $role->guard_name }}</dd>
        <dt class="col-sm-3">{{ __('Permissions') }}</dt>
        <dd class="col-sm-9">
          @forelse($role->permissions as $p)
            <span class="badge bg-label-secondary me-1 mb-1">{{ $p->name }}</span>
          @empty
            <span class="text-muted">{{ __('None') }}</span>
          @endforelse
        </dd>
      </dl>
    </div>
  </div>
</div>
@endsection
