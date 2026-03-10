{{-- ALOS-S1-20 / ALOS-S1-21 — صفحة رئيسية للموقع الخارجي — تستخدم tenant_settings --}}
@extends('core::layouts.layoutMaster')

@section('title', $settings->getDisplayName())

@php
  $hex = $settings->primary_color ? ltrim($settings->primary_color, '#') : '0d6efd';
  if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
  $primary = '#' . $hex;
  $parts = str_split($hex, 2);
  $primaryRgb = count($parts) >= 3 ? implode(',', [hexdec($parts[0]), hexdec($parts[1]), hexdec($parts[2])]) : '13,110,253';
@endphp

@section('page-style')
<style>
  .landing-simple { min-height: 60vh; display: flex; align-items: center; padding-top: 10rem; }
  .tenant-brand-primary { --bs-primary: {{ $primary }}; --bs-primary-rgb: {{ $primaryRgb }}; }
  .tenant-brand-primary .btn-primary { background-color: {{ $primary }}; border-color: {{ $primary }}; }
  .tenant-brand-primary .btn-primary:hover { filter: brightness(0.9); }
  .tenant-brand-primary .btn-outline-primary { border-color: {{ $primary }}; color: {{ $primary }}; }
</style>
@endsection

@section('content')
<div class="landing-simple py-5 tenant-brand-primary">
  <div class="container">
    <div class="row align-items-center justify-content-center g-4">
      <div class="col-lg-6 text-center">
        @if ($settings->logo_url)
          <img src="{{ $settings->logo_url }}" alt="{{ $settings->getDisplayName() }}" class="img-fluid rounded mb-4" style="max-height: 120px;">
        @else
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-4" style="width: 100px; height: 100px;">
            <i class="icon-base ti tabler-building-store icon-32px"></i>
          </span>
        @endif
        <h1 class="mb-3 fw-bold">{{ $settings->getDisplayName() }}</h1>
        <p class="text-muted mb-4">
          {{ $settings->short_description ?? $tenant->description ?? __('Legal office management') }}
        </p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
          <a href="{{ url('/portal/login') }}" class="btn btn-outline-primary">{{ __('Log in') }}</a>
          <a href="{{ url('/portal/login') }}" class="btn btn-primary">{{ __('Get started') }}</a>
        </div>
        @if ($settings->email || $settings->phone)
          <div class="small text-muted">
            @if ($settings->email)<a href="mailto:{{ $settings->email }}" class="text-muted text-decoration-none">{{ $settings->email }}</a>@endif
            @if ($settings->email && $settings->phone) · @endif
            @if ($settings->phone)<a href="tel:{{ $settings->phone }}" class="text-muted text-decoration-none">{{ $settings->phone }}</a>@endif
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
