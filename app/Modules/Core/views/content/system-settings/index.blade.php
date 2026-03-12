@extends('core::layouts.layoutMaster')

@section('title', __('System Settings') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('System Settings') }}</h4>
      <p class="text-body mb-0 small">{{ __('Global platform configuration. These settings apply to the entire ALOS platform, not to individual tenants.') }}</p>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <ul class="nav nav-tabs nav-fill mb-4" role="tablist">
        <li class="nav-item">
          <a class="nav-link {{ ($activeTab ?? 'general') === 'general' ? 'active' : '' }}" href="{{ route('admin.core.system-settings.index', ['tab' => 'general']) }}">
            <i class="icon-base ti tabler-brand-apple me-1"></i>{{ __('General') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($activeTab ?? '') === 'mail' ? 'active' : '' }}" href="{{ route('admin.core.system-settings.index', ['tab' => 'mail']) }}">
            <i class="icon-base ti tabler-mail me-1"></i>{{ __('Mail') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($activeTab ?? '') === 'notifications' ? 'active' : '' }}" href="{{ route('admin.core.system-settings.index', ['tab' => 'notifications']) }}">
            <i class="icon-base ti tabler-bell me-1"></i>{{ __('Notifications') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($activeTab ?? '') === 'storage' ? 'active' : '' }}" href="{{ route('admin.core.system-settings.index', ['tab' => 'storage']) }}">
            <i class="icon-base ti tabler-device-floppy me-1"></i>{{ __('Storage') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($activeTab ?? '') === 'registration' ? 'active' : '' }}" href="{{ route('admin.core.system-settings.index', ['tab' => 'registration']) }}">
            <i class="icon-base ti tabler-user-plus me-1"></i>{{ __('Registration') }}
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ ($activeTab ?? '') === 'branding' ? 'active' : '' }}" href="{{ route('admin.core.system-settings.index', ['tab' => 'branding']) }}">
            <i class="icon-base ti tabler-palette me-1"></i>{{ __('Branding') }}
          </a>
        </li>
      </ul>

      {{-- General --}}
      @if (($activeTab ?? 'general') === 'general')
      <form action="{{ route('admin.core.system-settings.update') }}" method="post" class="mb-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="general" />
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="system_name">{{ __('System name') }}</label>
            <input type="text" class="form-control" id="system_name" name="system_name" value="{{ old('system_name', $general['system_name'] ?? config('app.name')) }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="support_email">{{ __('Support email') }}</label>
            <input type="email" class="form-control" id="support_email" name="support_email" value="{{ old('support_email', $general['support_email'] ?? '') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="support_phone">{{ __('Support phone') }}</label>
            <input type="text" class="form-control" id="support_phone" name="support_phone" value="{{ old('support_phone', $general['support_phone'] ?? '') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="default_language">{{ __('Default language') }}</label>
            <input type="text" class="form-control" id="default_language" name="default_language" value="{{ old('default_language', $general['default_language'] ?? config('app.locale')) }}" placeholder="en" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="timezone">{{ __('Timezone') }}</label>
            <input type="text" class="form-control" id="timezone" name="timezone" value="{{ old('timezone', $general['timezone'] ?? config('app.timezone')) }}" placeholder="UTC" />
          </div>
          <div class="col-12">
            <label class="form-label" for="system_logo">{{ __('System logo URL') }}</label>
            <input type="text" class="form-control" id="system_logo" name="system_logo" value="{{ old('system_logo', $general['system_logo'] ?? '') }}" placeholder="https://..." />
          </div>
          <div class="col-12">
            <label class="form-label" for="favicon">{{ __('Favicon URL') }}</label>
            <input type="text" class="form-control" id="favicon" name="favicon" value="{{ old('favicon', $general['favicon'] ?? '') }}" placeholder="https://..." />
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ __('Save general settings') }}</button>
          </div>
        </div>
      </form>
      @endif

      {{-- Mail --}}
      @if (($activeTab ?? '') === 'mail')
      <form action="{{ route('admin.core.system-settings.update') }}" method="post" class="mb-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="mail" />
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="mail_driver">{{ __('Mail driver') }}</label>
            <input type="text" class="form-control" id="mail_driver" name="mail_driver" value="{{ old('mail_driver', $mail['mail_driver'] ?? 'smtp') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_host">{{ __('Mail host') }}</label>
            <input type="text" class="form-control" id="mail_host" name="mail_host" value="{{ old('mail_host', $mail['mail_host'] ?? '') }}" placeholder="smtp.example.com" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_port">{{ __('Mail port') }}</label>
            <input type="text" class="form-control" id="mail_port" name="mail_port" value="{{ old('mail_port', $mail['mail_port'] ?? '587') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_username">{{ __('Mail username') }}</label>
            <input type="text" class="form-control" id="mail_username" name="mail_username" value="{{ old('mail_username', $mail['mail_username'] ?? '') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_password">{{ __('Mail password') }}</label>
            <input type="password" class="form-control" id="mail_password" name="mail_password" value="" placeholder="{{ __('Leave blank to keep current') }}" autocomplete="new-password" />
            <small class="text-muted">{{ __('Only fill to change the stored password.') }}</small>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_encryption">{{ __('Encryption') }}</label>
            <select class="form-select" id="mail_encryption" name="mail_encryption">
              <option value="tls" {{ old('mail_encryption', $mail['mail_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
              <option value="ssl" {{ old('mail_encryption', $mail['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
              <option value="" {{ old('mail_encryption', $mail['mail_encryption'] ?? '') === '' ? 'selected' : '' }}>{{ __('None') }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_from_address">{{ __('From address') }}</label>
            <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $mail['mail_from_address'] ?? '') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="mail_from_name">{{ __('From name') }}</label>
            <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $mail['mail_from_name'] ?? '') }}" />
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary me-2">{{ __('Save mail settings') }}</button>
          </div>
        </div>
      </form>
      <hr />
      <h6 class="mb-2">{{ __('Test email') }}</h6>
      <form action="{{ route('admin.core.system-settings.test-mail') }}" method="post" class="row g-2 align-items-end">
        @csrf
        <div class="col-auto">
          <label class="form-label mb-0" for="test_email">{{ __('Send test to') }}</label>
        </div>
        <div class="col-auto">
          <input type="email" class="form-control" id="test_email" name="email" required placeholder="email@example.com" />
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-outline-primary">{{ __('Send test email') }}</button>
        </div>
      </form>
      @endif

      {{-- Notifications --}}
      @if (($activeTab ?? '') === 'notifications')
      <form action="{{ route('admin.core.system-settings.update') }}" method="post" class="mb-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="notifications" />
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="enable_in_app_notifications" name="enable_in_app_notifications" value="1" {{ old('enable_in_app_notifications', $notifications['enable_in_app_notifications'] ?? true) ? 'checked' : '' }} />
            <label class="form-check-label" for="enable_in_app_notifications">{{ __('Enable in-app notifications') }}</label>
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="enable_email_notifications" name="enable_email_notifications" value="1" {{ old('enable_email_notifications', $notifications['enable_email_notifications'] ?? true) ? 'checked' : '' }} />
            <label class="form-check-label" for="enable_email_notifications">{{ __('Enable email notifications') }}</label>
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="enable_session_reminders" name="enable_session_reminders" value="1" {{ old('enable_session_reminders', $notifications['enable_session_reminders'] ?? true) ? 'checked' : '' }} />
            <label class="form-check-label" for="enable_session_reminders">{{ __('Enable session reminders') }}</label>
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="enable_auto_reports" name="enable_auto_reports" value="1" {{ old('enable_auto_reports', $notifications['enable_auto_reports'] ?? false) ? 'checked' : '' }} />
            <label class="form-check-label" for="enable_auto_reports">{{ __('Enable auto reports') }}</label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Save notification settings') }}</button>
      </form>
      @endif

      {{-- Storage --}}
      @if (($activeTab ?? '') === 'storage')
      <form action="{{ route('admin.core.system-settings.update') }}" method="post" class="mb-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="storage" />
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label" for="storage_driver">{{ __('Storage driver') }}</label>
            <input type="text" class="form-control" id="storage_driver" name="storage_driver" value="{{ old('storage_driver', $storage['storage_driver'] ?? 'local') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="max_upload_size">{{ __('Max upload size (KB)') }}</label>
            <input type="number" class="form-control" id="max_upload_size" name="max_upload_size" value="{{ old('max_upload_size', $storage['max_upload_size'] ?? 10240) }}" min="1" max="512000" />
          </div>
          <div class="col-12">
            <label class="form-label" for="allowed_file_types">{{ __('Allowed file types (comma-separated)') }}</label>
            <input type="text" class="form-control" id="allowed_file_types" name="allowed_file_types" value="{{ old('allowed_file_types', is_array($storage['allowed_file_types'] ?? null) ? implode(',', $storage['allowed_file_types']) : ($storage['allowed_file_types'] ?? 'pdf,doc,docx,jpg,png')) }}" placeholder="pdf,doc,docx,jpg,png" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="public_storage_disk">{{ __('Public storage disk') }}</label>
            <input type="text" class="form-control" id="public_storage_disk" name="public_storage_disk" value="{{ old('public_storage_disk', $storage['public_storage_disk'] ?? 'public') }}" />
          </div>
          <div class="col-md-6">
            <label class="form-label" for="private_storage_disk">{{ __('Private storage disk') }}</label>
            <input type="text" class="form-control" id="private_storage_disk" name="private_storage_disk" value="{{ old('private_storage_disk', $storage['private_storage_disk'] ?? 'local') }}" />
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ __('Save storage settings') }}</button>
          </div>
        </div>
      </form>
      @endif

      {{-- Registration --}}
      @if (($activeTab ?? '') === 'registration')
      <form action="{{ route('admin.core.system-settings.update') }}" method="post" class="mb-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="registration" />
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="allow_tenant_registration" name="allow_tenant_registration" value="1" {{ old('allow_tenant_registration', $registration['allow_tenant_registration'] ?? true) ? 'checked' : '' }} />
            <label class="form-check-label" for="allow_tenant_registration">{{ __('Allow new tenant registration from public site') }}</label>
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification" value="1" {{ old('require_email_verification', $registration['require_email_verification'] ?? false) ? 'checked' : '' }} />
            <label class="form-check-label" for="require_email_verification">{{ __('Require email verification for new tenants') }}</label>
          </div>
        </div>
        <div class="mb-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="allow_trial_accounts" name="allow_trial_accounts" value="1" {{ old('allow_trial_accounts', $registration['allow_trial_accounts'] ?? true) ? 'checked' : '' }} />
            <label class="form-check-label" for="allow_trial_accounts">{{ __('Allow trial accounts') }}</label>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label" for="default_subscription_plan_id">{{ __('Default subscription plan (for new tenants)') }}</label>
          <select class="form-select" id="default_subscription_plan_id" name="default_subscription_plan_id">
            <option value="">{{ __('— None —') }}</option>
            @foreach($subscriptionPlans ?? [] as $plan)
              <option value="{{ $plan->id }}" {{ old('default_subscription_plan_id', $registration['default_subscription_plan_id'] ?? '') == $plan->id ? 'selected' : '' }}>{{ $plan->plan_name }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('Save registration settings') }}</button>
      </form>
      @endif

      {{-- Branding (alias for logo/favicon) --}}
      @if (($activeTab ?? '') === 'branding')
      <form action="{{ route('admin.core.system-settings.update') }}" method="post" class="mb-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="group" value="branding" />
        <p class="text-muted small">{{ __('These values affect the platform identity (e.g. login page, landing). For tenant-specific branding use each tenant’s branding settings.') }}</p>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label" for="branding_system_logo">{{ __('System logo URL') }}</label>
            <input type="text" class="form-control" id="branding_system_logo" name="system_logo" value="{{ old('system_logo', $branding['system_logo'] ?? $general['system_logo'] ?? '') }}" placeholder="https://..." />
          </div>
          <div class="col-12">
            <label class="form-label" for="branding_favicon">{{ __('Favicon URL') }}</label>
            <input type="text" class="form-control" id="branding_favicon" name="favicon" value="{{ old('favicon', $branding['favicon'] ?? $general['favicon'] ?? '') }}" placeholder="https://..." />
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ __('Save branding') }}</button>
          </div>
        </div>
      </form>
      @endif
    </div>
  </div>
</div>
@endsection
