@php
  $configData = Helper::appClasses();
  $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z0-9\p{Arabic}]/u', '', $tenant->name), 0, 2) ?: $tenant->slug);
  if (mb_strlen($initials) > 2) $initials = mb_substr($initials, 0, 2);
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $tenant->name . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Tenant Details') }}</h4>
    <div class="d-flex gap-2">
      <a href="{{ Helper::tenantPublicUrl($tenant) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
        <i class="icon-base ti tabler-external-link me-1"></i>{{ __('View Public Site') }}
      </a>
      <a href="{{ route('admin.core.tenants.edit', $tenant) }}" class="btn btn-warning btn-sm">
        <i class="icon-base ti tabler-pencil me-1"></i>{{ __('Edit') }}
      </a>
      <a href="{{ route('admin.core.tenants.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
    </div>
  </div>

  <div class="row g-4">
    {{-- بطاقة المعلومات الأساسية --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="icon-base ti tabler-building-store"></i>
          <h5 class="mb-0">{{ __('Basic Info') }}</h5>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center gap-3 mb-4">
            @if ($settings->logo_url)
              <img src="{{ $settings->logo_url }}" alt="{{ $settings->getDisplayName() }}" class="rounded" style="height: 64px;">
            @else
              <span class="avatar avatar-lg"><span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span></span>
            @endif
            <div>
              <h5 class="mb-1">{{ $tenant->name }}</h5>
              <p class="text-muted mb-0 small"><code>{{ $tenant->slug }}</code></p>
              <span class="badge {{ $tenant->is_active ? 'bg-label-success' : 'bg-label-danger' }} mt-1">
                {{ $tenant->is_active ? __('Active') : __('Inactive') }}
              </span>
            </div>
          </div>
          <dl class="row mb-0">
            <dt class="col-sm-4">{{ __('Name') }}</dt>
            <dd class="col-sm-8">{{ $tenant->name }}</dd>
            <dt class="col-sm-4">{{ __('Slug') }}</dt>
            <dd class="col-sm-8"><code>{{ $tenant->slug }}</code> <a href="{{ Helper::tenantPublicUrl($tenant) }}" target="_blank" rel="noopener" class="ms-1 small">/{{ $tenant->slug }}</a></dd>
            <dt class="col-sm-4">{{ __('Username') }}</dt>
            <dd class="col-sm-8">{{ $tenant->username ?? '—' }}</dd>
            <dt class="col-sm-4">{{ __('Domain') }}</dt>
            <dd class="col-sm-8">{{ $tenant->domain ?? '—' }}</dd>
            <dt class="col-sm-4">{{ __('Plan') }}</dt>
            <dd class="col-sm-8">{{ $tenant->plan ? __(ucfirst($tenant->plan)) : '—' }}</dd>
            <dt class="col-sm-4">{{ __('Public site enabled') }}</dt>
            <dd class="col-sm-8">
              <span class="badge {{ $settings->hasPublicSiteEnabled() ? 'bg-label-success' : 'bg-label-secondary' }}">
                {{ $settings->hasPublicSiteEnabled() ? __('Yes') : __('No') }}
              </span>
            </dd>
            <dt class="col-sm-4">{{ __('Users') }}</dt>
            <dd class="col-sm-8">{{ $tenant->users_count ?? $tenant->users()->count() }}</dd>
            <dt class="col-sm-4">{{ __('Clients') }}</dt>
            <dd class="col-sm-8">{{ $tenant->clients_count ?? $tenant->clients()->count() }}</dd>
            <dt class="col-sm-4">{{ __('Created') }}</dt>
            <dd class="col-sm-8">{{ $tenant->created_at?->format('Y-m-d H:i') }}</dd>
          </dl>
        </div>
      </div>
    </div>

    {{-- بطاقة الموقع العام والعلامة التجارية --}}
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="icon-base ti tabler-world"></i>
          <h5 class="mb-0">{{ __('Public Site / Branding') }}</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">{{ __('Display name') }}</dt>
            <dd class="col-sm-8">{{ $settings->getDisplayName() }}</dd>
            <dt class="col-sm-4">{{ __('Short description') }}</dt>
            <dd class="col-sm-8">{{ $settings->short_description ?? $tenant->description ?? '—' }}</dd>
            <dt class="col-sm-4">{{ __('Primary color') }}</dt>
            <dd class="col-sm-8">
              @if ($settings->primary_color)
                <span class="badge" style="background-color:#{{ $settings->primary_color }};color:#fff">#{{ $settings->primary_color }}</span>
              @else
                —
              @endif
            </dd>
            <dt class="col-sm-4">{{ __('Secondary color') }}</dt>
            <dd class="col-sm-8">
              @if ($settings->secondary_color)
                <span class="badge" style="background-color:#{{ $settings->secondary_color }};color:#fff">#{{ $settings->secondary_color }}</span>
              @else
                —
              @endif
            </dd>
          </dl>
        </div>
      </div>
    </div>

    {{-- بطاقة معلومات التواصل --}}
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="icon-base ti tabler-phone"></i>
          <h5 class="mb-0">{{ __('Contact info') }}</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small text-muted">{{ __('Email') }}</label>
              <p class="mb-0">{{ $settings->email ?? $tenant->email ?? '—' }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted">{{ __('Phone') }}</label>
              <p class="mb-0">{{ $settings->phone ?? $tenant->phone ?? '—' }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted">{{ __('WhatsApp') }}</label>
              <p class="mb-0">{{ $settings->whatsapp ?? '—' }}</p>
            </div>
            <div class="col-md-6">
              <label class="form-label small text-muted">{{ __('City') }}</label>
              <p class="mb-0">{{ $settings->city ?? $tenant->city ?? '—' }}</p>
            </div>
            <div class="col-12">
              <label class="form-label small text-muted">{{ __('Address') }}</label>
              <p class="mb-0">{{ $settings->address ?? '—' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
