@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Law Firm') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add Law Firm') }}</h4>
    <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-right me-1"></i>
      {{ __('Back to list') }}
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.core.tenants.store') }}" method="post">
        @csrf
        <h6 class="mb-3">{{ __('Company info') }}</h6>
        <div class="mb-3">
          <label for="name" class="form-label">{{ __('Company name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="slug" class="form-label">{{ __('Slug / subdomain') }} <span class="text-danger">*</span></label>
            <input type="text" name="slug" id="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" required maxlength="255" pattern="[a-z0-9\-]+" placeholder="e.g. my-firm">
            <div class="form-text">{{ __('Lowercase letters, numbers and hyphens only.') }}</div>
            @error('slug')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="username" class="form-label">{{ __('Username') }}</label>
            <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" maxlength="64" placeholder="e.g. myoffice">
            @error('username')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="mb-3">
          <label for="domain" class="form-label">{{ __('Domain') }}</label>
          <input type="text" name="domain" id="domain" class="form-control @error('domain') is-invalid @enderror" value="{{ old('domain') }}" maxlength="255" placeholder="e.g. myoffice.com">
          @error('domain')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <hr class="my-4">
        <h6 class="mb-3">{{ __('Subscription & contract') }}</h6>
        <div class="mb-3">
          <label for="subscription_plan_id" class="form-label">{{ __('Subscription plan') }}</label>
          <select name="subscription_plan_id" id="subscription_plan_id" class="form-select">
            <option value="">{{ __('— None —') }}</option>
            @foreach($subscriptionPlans ?? [] as $plan)
              <option value="{{ $plan->id }}" {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->plan_name }} — {{ number_format($plan->price ?? 0, 2) }}</option>
            @endforeach
          </select>
          <div class="form-text">{{ __('Billing cycle and plan price can be set below.') }}</div>
          @error('subscription_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="contract_start_date" class="form-label">{{ __('Contract start date') }}</label>
            <input type="date" name="contract_start_date" id="contract_start_date" class="form-control @error('contract_start_date') is-invalid @enderror" value="{{ old('contract_start_date') }}">
            @error('contract_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="contract_end_date" class="form-label">{{ __('Contract end date') }}</label>
            <input type="date" name="contract_end_date" id="contract_end_date" class="form-control @error('contract_end_date') is-invalid @enderror" value="{{ old('contract_end_date') }}">
            @error('contract_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="billing_cycle" class="form-label">{{ __('Billing cycle') }}</label>
            <select name="billing_cycle" id="billing_cycle" class="form-select">
              <option value="">{{ __('—') }}</option>
              <option value="monthly" {{ old('billing_cycle') === 'monthly' ? 'selected' : '' }}>{{ __('Monthly') }}</option>
              <option value="yearly" {{ old('billing_cycle') === 'yearly' ? 'selected' : '' }}>{{ __('Yearly') }}</option>
            </select>
            @error('billing_cycle')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="plan_price" class="form-label">{{ __('Plan price') }}</label>
            <input type="number" name="plan_price" id="plan_price" class="form-control @error('plan_price') is-invalid @enderror" value="{{ old('plan_price') }}" min="0" step="0.01" placeholder="0.00">
            @error('plan_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
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
        <div class="mb-3">
          <label for="status" class="form-label">{{ __('Status') }}</label>
          <select name="status" id="status" class="form-select">
            @foreach(\App\Models\Tenant::STATUSES as $s)
              <option value="{{ $s }}" {{ old('status', \App\Models\Tenant::STATUS_ACTIVE) === $s ? 'selected' : '' }}>{{ __(ucfirst($s)) }}</option>
            @endforeach
          </select>
          <div class="form-text">{{ __('Active = firm can sign in. Suspended/Inactive = access restricted.') }}</div>
          @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <div class="form-check">
            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" {{ old('is_active', true) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">{{ __('Active (users can sign in)') }}</label>
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
          @error('public_site_enabled')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <hr class="my-4">
        <h6 class="mb-3">{{ __('Contact & location') }}</h6>
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
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="city" class="form-label">{{ __('City') }}</label>
            <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city') }}" maxlength="128">
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="country" class="form-label">{{ __('Country') }}</label>
            <input type="text" name="country" id="country" class="form-control @error('country') is-invalid @enderror" value="{{ old('country') }}" maxlength="100">
            @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <hr class="my-4">
        <h6 class="mb-3">{{ __('Public site (optional)') }}</h6>
        <div class="mb-3">
          <label for="logo" class="form-label">{{ __('Logo URL') }}</label>
          <input type="url" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" value="{{ old('logo') }}" maxlength="500" placeholder="https://...">
          @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">{{ __('Description') }}</label>
          <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
          @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Create Law Firm') }}</button>
          <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
