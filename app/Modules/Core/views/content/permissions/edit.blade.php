@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Edit Permission') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Edit Permission') }}</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('core.permissions.show', $permission) }}" class="btn btn-outline-secondary btn-sm">{{ __('View') }}</a>
      <a href="{{ route('core.permissions.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('core.permissions.update', $permission) }}" method="post">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $permission->name) }}" required maxlength="255">
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Update Permission') }}</button>
          <a href="{{ route('core.permissions.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
