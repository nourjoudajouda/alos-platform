@php
  $configData = Helper::appClasses();
  $clientRoutePrefix = $clientRoutePrefix ?? 'admin.core.clients';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Client') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add Client') }}</h4>
    <a href="{{ route($clientRoutePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-right me-1"></i>
      {{ __('Back to list') }}
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route($clientRoutePrefix . '.store') }}" method="post">
        @csrf
        @if($tenants->isNotEmpty())
        <div class="mb-3">
          <label for="tenant_id" class="form-label">{{ __('Tenant') }}</label>
          <select name="tenant_id" id="tenant_id" class="form-select">
            <option value="">{{ __('None') }}</option>
            @foreach($tenants as $t)
              <option value="{{ $t->id }}" {{ old('tenant_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
          </select>
          <div class="form-text small">{{ __('Office this client belongs to. Optional.') }}</div>
          @error('tenant_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>
        @endif
        <div class="mb-3">
          <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">{{ __('Email') }}</label>
          <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" maxlength="255">
          @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="phone" class="form-label">{{ __('Phone') }}</label>
          <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" maxlength="50">
          @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Create Client') }}</button>
          <a href="{{ route($clientRoutePrefix . '.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
