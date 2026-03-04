@php
  $configData = Helper::appClasses();
  $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z0-9\p{Arabic}]/u', '', $tenant->name), 0, 2) ?: $tenant->slug);
  if (mb_strlen($initials) > 2) $initials = mb_substr($initials, 0, 2);
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $tenant->name . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">Tenant Details</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('core.tenants.edit', $tenant) }}" class="btn btn-warning btn-sm">
        <i class="icon-base ti tabler-pencil me-1"></i>
        Edit
      </a>
      <a href="{{ route('core.tenants.index') }}" class="btn btn-outline-secondary btn-sm">Back to list</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-4">
        <span class="avatar avatar-lg">
          <span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span>
        </span>
        <div>
          <h5 class="mb-1">{{ $tenant->name }}</h5>
          <p class="text-muted mb-0 small">{{ $tenant->slug }}</p>
          <span class="badge bg-label-secondary mt-1">Created {{ $tenant->created_at?->format('Y-m-d') }}</span>
        </div>
      </div>
      <hr>
      <dl class="row mb-0">
        <dt class="col-sm-3">Name</dt>
        <dd class="col-sm-9">{{ $tenant->name }}</dd>
        <dt class="col-sm-3">Slug</dt>
        <dd class="col-sm-9"><code>{{ $tenant->slug }}</code></dd>
        <dt class="col-sm-3">Users</dt>
        <dd class="col-sm-9">{{ $tenant->users()->count() }}</dd>
        <dt class="col-sm-3">Created</dt>
        <dd class="col-sm-9">{{ $tenant->created_at?->format('Y-m-d H:i') }}</dd>
      </dl>
    </div>
  </div>
</div>
@endsection
