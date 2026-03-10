@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $permission->name . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0"><code>{{ $permission->name }}</code></h4>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.core.permissions.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">{{ __('Name') }}</dt>
        <dd class="col-sm-9"><code>{{ $permission->name }}</code></dd>
        <dt class="col-sm-3">{{ __('Guard') }}</dt>
        <dd class="col-sm-9">{{ $permission->guard_name }}</dd>
        <dt class="col-sm-3">{{ __('Roles with this permission') }}</dt>
        <dd class="col-sm-9">
          @forelse($permission->roles as $r)
            <a href="{{ route('admin.core.roles.show', $r) }}" class="badge bg-label-primary me-1 mb-1">{{ $r->name }}</a>
          @empty
            <span class="text-muted">{{ __('None') }}</span>
          @endforelse
        </dd>
      </dl>
    </div>
  </div>
</div>
@endsection
