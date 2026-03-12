@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Subscription Plan') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add Subscription Plan') }}</h4>
    <a href="{{ route('admin.core.subscription-plans.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back to list') }}
    </a>
  </div>

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.core.subscription-plans.store') }}" method="post">
        @csrf
        <div class="mb-3">
          <label for="plan_name" class="form-label">{{ __('Plan name') }} <span class="text-danger">*</span></label>
          <input type="text" name="plan_name" id="plan_name" class="form-control @error('plan_name') is-invalid @enderror" value="{{ old('plan_name') }}" required maxlength="100" placeholder="e.g. Basic, Professional">
          @error('plan_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="price" class="form-label">{{ __('Price') }} <span class="text-danger">*</span></label>
          <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required min="0" step="0.01" placeholder="0.00">
          @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label for="user_limit" class="form-label">{{ __('User limit') }} <span class="text-danger">*</span></label>
            <input type="number" name="user_limit" id="user_limit" class="form-control @error('user_limit') is-invalid @enderror" value="{{ old('user_limit', 5) }}" required min="0" placeholder="0 = {{ __('Unlimited') }}">
            @error('user_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4 mb-3">
            <label for="lawyer_limit" class="form-label">{{ __('Lawyer limit') }} <span class="text-danger">*</span></label>
            <input type="number" name="lawyer_limit" id="lawyer_limit" class="form-control @error('lawyer_limit') is-invalid @enderror" value="{{ old('lawyer_limit', 2) }}" required min="0" placeholder="0 = {{ __('Unlimited') }}">
            @error('lawyer_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4 mb-3">
            <label for="storage_limit" class="form-label">{{ __('Storage limit (MB)') }} <span class="text-danger">*</span></label>
            <input type="number" name="storage_limit" id="storage_limit" class="form-control @error('storage_limit') is-invalid @enderror" value="{{ old('storage_limit', 512) }}" required min="0" placeholder="1024 = 1 GB">
            @error('storage_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('Limits (0 = unlimited)') }}</label>
          <div class="row g-2">
            <div class="col-md-3 col-6">
              <label for="limits_max_admins" class="form-label small">{{ __('Max Admins') }}</label>
              <input type="number" name="limits_max_admins" id="limits_max_admins" class="form-control" value="{{ old('limits_max_admins', 0) }}" min="0">
            </div>
            <div class="col-md-3 col-6">
              <label for="limits_max_secretaries" class="form-label small">{{ __('Max Secretaries') }}</label>
              <input type="number" name="limits_max_secretaries" id="limits_max_secretaries" class="form-control" value="{{ old('limits_max_secretaries', 0) }}" min="0">
            </div>
            <div class="col-md-3 col-6">
              <label for="limits_max_accountants" class="form-label small">{{ __('Max Accountants') }}</label>
              <input type="number" name="limits_max_accountants" id="limits_max_accountants" class="form-control" value="{{ old('limits_max_accountants', 0) }}" min="0">
            </div>
            <div class="col-md-3 col-6">
              <label for="limits_max_trainees" class="form-label small">{{ __('Max Trainees') }}</label>
              <input type="number" name="limits_max_trainees" id="limits_max_trainees" class="form-control" value="{{ old('limits_max_trainees', 0) }}" min="0">
            </div>
            <div class="col-md-3 col-6">
              <label for="limits_max_clients" class="form-label small">{{ __('Max Clients') }}</label>
              <input type="number" name="limits_max_clients" id="limits_max_clients" class="form-control" value="{{ old('limits_max_clients', 0) }}" min="0">
            </div>
            <div class="col-md-3 col-6">
              <label for="limits_max_cases" class="form-label small">{{ __('Max Cases') }}</label>
              <input type="number" name="limits_max_cases" id="limits_max_cases" class="form-control" value="{{ old('limits_max_cases', 0) }}" min="0">
            </div>
            <div class="col-md-3 col-6">
              <label for="limits_max_documents" class="form-label small">{{ __('Max Documents') }}</label>
              <input type="number" name="limits_max_documents" id="limits_max_documents" class="form-control" value="{{ old('limits_max_documents', 0) }}" min="0">
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('Assign Features') }}</label>
          <div class="row g-2">
            @foreach($featureOptions ?? [] as $key => $label)
              <div class="col-md-6 col-lg-4">
                <div class="form-check">
                  <input type="checkbox" name="feature_{{ $key }}" id="feature_{{ $key }}" value="1" class="form-check-input" {{ old('feature_'.$key, false) ? 'checked' : '' }}>
                  <label for="feature_{{ $key }}" class="form-check-label">{{ $label }}</label>
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <div class="mb-3">
          <label for="features_json" class="form-label">{{ __('Extra features (JSON, optional)') }}</label>
          <textarea name="features_json" id="features_json" class="form-control font-monospace @error('features_json') is-invalid @enderror" rows="2" placeholder='{}'>{{ old('features_json', '{}') }}</textarea>
          <div class="form-text">{{ __('Optional. Merge with checkboxes above.') }}</div>
          @error('features_json')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Create Plan') }}</button>
          <a href="{{ route('admin.core.subscription-plans.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
