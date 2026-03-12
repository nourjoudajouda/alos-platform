@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Tenant') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">Add Tenant</h4>
    <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-right me-1"></i>
      Back to list
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.core.tenants.store') }}" method="post">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
          <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" required maxlength="255" pattern="[a-z0-9\-]+" placeholder="e.g. my-tenant">
          <div class="form-text">Lowercase letters, numbers and hyphens only.</div>
          @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="username" class="form-label">{{ __('Username') }}</label>
          <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" maxlength="64" placeholder="e.g. myoffice">
          <div class="form-text">{{ __('Optional. Unique. If set, domain will be generated from it.') }}</div>
          @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="domain" class="form-label">{{ __('Domain') }}</label>
          <input type="text" name="domain" id="domain" class="form-control @error('domain') is-invalid @enderror" value="{{ old('domain') }}" maxlength="255" placeholder="e.g. myoffice.com">
          <div class="form-text">{{ __('Optional. Custom domain for this tenant\'s panel and external site.') }}</div>
          @error('domain')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <label for="plan" class="form-label">{{ __('Plan') }}</label>
          <select name="plan" id="plan" class="form-select">
            <option value="">{{ __('All') }}</option>
            @foreach(\App\Models\Tenant::PLANS as $p)
              <option value="{{ $p }}" {{ old('plan') === $p ? 'selected' : '' }}>{{ __(ucfirst($p)) }}</option>
            @endforeach
          </select>
          @error('plan')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <hr class="my-4">
        <h6 class="mb-3">{{ __('Subscription') }}</h6>
        <div class="mb-3">
          <label for="subscription_plan_id" class="form-label">{{ __('Subscription plan') }}</label>
          <select name="subscription_plan_id" id="subscription_plan_id" class="form-select">
            <option value="">{{ __('— None —') }}</option>
            @foreach($subscriptionPlans ?? [] as $plan)
              <option value="{{ $plan->id }}" {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->plan_name }} ({{ number_format($plan->price, 2) }})</option>
            @endforeach
          </select>
          @error('subscription_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="subscription_status" class="form-label">{{ __('Subscription status') }}</label>
          <select name="subscription_status" id="subscription_status" class="form-select">
            <option value="active" {{ old('subscription_status', 'active') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
            <option value="trial" {{ old('subscription_status') === 'trial' ? 'selected' : '' }}>{{ __('Trial') }}</option>
            <option value="suspended" {{ old('subscription_status') === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
            <option value="expired" {{ old('subscription_status') === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
          </select>
          @error('subscription_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="start_date" class="form-label">{{ __('Contract start date') }}</label>
            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date') }}">
            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="end_date" class="form-label">{{ __('Contract end date') }}</label>
            <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date') }}">
            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">{{ __('Active (users can sign in from /login)') }}</label>
          </div>
          @error('is_active')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3">
          <div class="form-check">
            <input type="checkbox" name="public_site_enabled" id="public_site_enabled" value="1" class="form-check-input" {{ old('public_site_enabled', true) ? 'checked' : '' }}>
            <label for="public_site_enabled" class="form-check-label">{{ __('Public site enabled') }}</label>
          </div>
          <div class="form-text">{{ __('When enabled, office users see "Visit My Website" in the user menu.') }}</div>
          @error('public_site_enabled')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <hr class="my-4">
        <h6 class="mb-3">{{ __('Public site info') }}</h6>
        <div class="mb-3">
          <label for="logo" class="form-label">{{ __('Logo URL') }}</label>
          <input type="url" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" value="{{ old('logo') }}" maxlength="500" placeholder="https://...">
          @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">{{ __('Description') }}</label>
          <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="{{ __('Short description for public site') }}">{{ old('description') }}</textarea>
          @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" maxlength="255">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">{{ __('Phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" maxlength="64">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="mb-3">
          <label for="city" class="form-label">{{ __('City') }}</label>
          <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" maxlength="128">
          @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">Create Tenant</button>
          <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
