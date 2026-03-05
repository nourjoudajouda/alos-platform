@php
  $configData = Helper::appClasses();
  $currentRole = $user->getRoleNames()->first();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Edit User') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Edit User') }}</h4>
    <a href="{{ route('identity.users.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('identity.users.update', $user) }}" method="post">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required maxlength="255">
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
          <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required maxlength="255">
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
          <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
            @foreach($internalRoles as $r)
              <option value="{{ $r->name }}" {{ old('role', $currentRole) === $r->name ? 'selected' : '' }}>
                {{ __(\Illuminate\Support\Str::title(str_replace('_', ' ', $r->name))) }}
              </option>
            @endforeach
          </select>
          @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">{{ __('New password') }} <span class="text-muted">({{ __('optional') }})</span></label>
          <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" minlength="8" autocomplete="new-password">
          <div class="form-text">{{ __('Leave blank to keep current password. Min 8 characters.') }}</div>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="password_confirmation" class="form-label">{{ __('Confirm new password') }}</label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Update User') }}</button>
          <a href="{{ route('identity.users.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
