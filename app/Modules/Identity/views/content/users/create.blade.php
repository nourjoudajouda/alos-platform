@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add User') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add User') }}</h4>
    <a href="{{ route('identity.users.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('identity.users.store') }}" method="post">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
          <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required maxlength="255">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
          <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" autocomplete="new-password">
          <div class="form-text">{{ __('Min 8 characters.') }}</div>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
          <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
            @foreach($internalRoles as $r)
              <option value="{{ $r->name }}" {{ old('role') === $r->name ? 'selected' : '' }}>
                {{ __(\Illuminate\Support\Str::title(str_replace('_', ' ', $r->name))) }}
              </option>
            @endforeach
          </select>
          @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Create User') }}</button>
          <a href="{{ route('identity.users.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
