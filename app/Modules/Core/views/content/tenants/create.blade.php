@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Tenant') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">Add Tenant</h4>
    <a href="{{ route('core.tenants.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-right me-1"></i>
      Back to list
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('core.tenants.store') }}" method="post">
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
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">Create Tenant</button>
          <a href="{{ route('core.tenants.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
