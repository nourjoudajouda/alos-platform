@extends('core::layouts.layoutMaster')

@section('title', __('Branding Settings') . ' — ' . ($tenant->name ?? config('app.name')))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Branding Settings') }}</h4>
      <p class="text-body mb-0 small">{{ __('Customize your public site logo, colors and contact info.') }}</p>
    </div>
    <a href="{{ Helper::tenantPublicUrl($tenant) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
      <i class="icon-base ti tabler-external-link me-1"></i>{{ __('View Public Site') }}
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form action="{{ route('company.settings.branding.update') }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <h6 class="mb-3">{{ __('Logo') }}</h6>
        <div class="mb-4">
          @if ($settings->logo_path)
            <div class="d-flex align-items-center gap-3 mb-2">
              <img src="{{ $settings->logo_url }}" alt="{{ $settings->getDisplayName() }}" class="rounded" style="max-height: 80px;">
              <div class="form-check">
                <input type="checkbox" name="remove_logo" id="remove_logo" value="1" class="form-check-input">
                <label for="remove_logo" class="form-check-label text-danger">{{ __('Remove logo') }}</label>
              </div>
            </div>
          @endif
          <input type="file" name="logo" id="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp">
          <div class="form-text">{{ __('PNG, JPG, GIF or WebP. Max 2MB.') }}</div>
          @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <h6 class="mb-3 mt-4">{{ __('Favicon') }}</h6>
        <div class="mb-4">
          @if ($settings->favicon_path)
            <div class="d-flex align-items-center gap-3 mb-2">
              <img src="{{ $settings->favicon_url }}" alt="Favicon" class="rounded" style="width: 32px; height: 32px;">
              <div class="form-check">
                <input type="checkbox" name="remove_favicon" id="remove_favicon" value="1" class="form-check-input">
                <label for="remove_favicon" class="form-check-label text-danger">{{ __('Remove favicon') }}</label>
              </div>
            </div>
          @endif
          <input type="file" name="favicon" id="favicon" class="form-control @error('favicon') is-invalid @enderror" accept="image/png,image/x-icon,image/vnd.microsoft.icon,.ico">
          <div class="form-text">{{ __('PNG or ICO. Shown in browser tab. Recommended: 32x32 or 16x16.') }}</div>
          @error('favicon')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <hr class="my-4">
        <h6 class="mb-3">{{ __('Public site info') }}</h6>
        <div class="mb-3">
          <label for="display_name" class="form-label">{{ __('Display name') }}</label>
          <input type="text" name="display_name" id="display_name" class="form-control @error('display_name') is-invalid @enderror" value="{{ old('display_name', $settings->display_name ?? $tenant->name) }}" maxlength="255" placeholder="{{ $tenant->name }}">
          <div class="form-text">{{ __('Shown on public site. Leave empty to use office name.') }}</div>
          @error('display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label for="short_description" class="form-label">{{ __('Short description') }}</label>
          <textarea name="short_description" id="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="3" placeholder="{{ __('About your office') }}">{{ old('short_description', $settings->short_description) }}</textarea>
          @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <hr class="my-4">
        <h6 class="mb-3">{{ __('Brand colors') }}</h6>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="primary_color" class="form-label">{{ __('Primary color') }}</label>
            <div class="input-group">
              <span class="input-group-text p-1" style="width: 40px; background: {{ $settings->primary_color ? '#' . $settings->primary_color : '#0d6efd' }};"></span>
              <input type="text" name="primary_color" id="primary_color" class="form-control @error('primary_color') is-invalid @enderror" value="{{ old('primary_color', $settings->primary_color ? '#' . $settings->primary_color : '') }}" maxlength="20" placeholder="#0d6efd">
            </div>
            @error('primary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="secondary_color" class="form-label">{{ __('Secondary color') }}</label>
            <div class="input-group">
              <span class="input-group-text p-1" style="width: 40px; background: {{ $settings->secondary_color ? '#' . $settings->secondary_color : '#6c757d' }};"></span>
              <input type="text" name="secondary_color" id="secondary_color" class="form-control @error('secondary_color') is-invalid @enderror" value="{{ old('secondary_color', $settings->secondary_color ? '#' . $settings->secondary_color : '') }}" maxlength="20" placeholder="#6c757d">
            </div>
            @error('secondary_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <hr class="my-4">
        <h6 class="mb-3">{{ __('Contact info') }}</h6>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $settings->email) }}" maxlength="255">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">{{ __('Phone') }}</label>
            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $settings->phone) }}" maxlength="64">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>
        <div class="mb-3">
          <label for="whatsapp" class="form-label">{{ __('WhatsApp') }}</label>
          <input type="text" name="whatsapp" id="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp', $settings->whatsapp) }}" maxlength="64" placeholder="+966...">
          @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="city" class="form-label">{{ __('City') }}</label>
            <input type="text" name="city" id="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $settings->city) }}" maxlength="128">
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6 mb-3">
            <label for="address" class="form-label">{{ __('Address') }}</label>
            <input type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $settings->address) }}" maxlength="500">
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <hr class="my-4">
        <div class="mb-4">
          <div class="form-check">
            <input type="checkbox" name="public_site_enabled" id="public_site_enabled" value="1" class="form-check-input" {{ old('public_site_enabled', $settings->public_site_enabled ?? true) ? 'checked' : '' }}>
            <label for="public_site_enabled" class="form-check-label">{{ __('Public site enabled') }}</label>
          </div>
          <div class="form-text">{{ __('When disabled, your public site returns 404.') }}</div>
          @error('public_site_enabled')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Save settings') }}</button>
          <a href="{{ route('company.dashboard') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
