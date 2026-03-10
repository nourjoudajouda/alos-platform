@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp

@extends('portal::layouts.portal')

@section('title', __('Dashboard') . ' — ' . __('Client Portal'))

@section('content')
  <div class="row">
    <div class="col-12">
      <h4 class="fw-bold mb-1">{{ __('Dashboard') }}</h4>
      <p class="text-muted mb-4">{{ __('Welcome') }}, {{ $user->name }}</p>
    </div>
  </div>

  {{-- Client info card --}}
  <div class="row mb-4">
    <div class="col-md-6 col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('My details') }}</h5>
        </div>
        <div class="card-body">
          <dl class="mb-0">
            <dt>{{ __('Name') }}</dt>
            <dd>{{ $client->name }}</dd>
            @if ($client->email)
              <dt>{{ __('Email') }}</dt>
              <dd>{{ $client->email }}</dd>
            @endif
            @if ($client->phone)
              <dt>{{ __('Phone') }}</dt>
              <dd>{{ $client->phone }}</dd>
            @endif
          </dl>
        </div>
      </div>
    </div>
  </div>

  {{-- Placeholders: Cases, Documents, Messages --}}
  <div class="row">
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-body text-center py-5">
          <i class="icon-base ti tabler-briefcase icon-32px text-muted d-block mb-3"></i>
          <h6 class="mb-2">{{ __('Cases') }}</h6>
          <p class="text-muted small mb-0">{{ __('This section will be available in a future release.') }}</p>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-lg-4 mb-4">
      <a href="{{ route('portal.documents.index') }}" class="card h-100 text-decoration-none text-body">
        <div class="card-body text-center py-5">
          <i class="icon-base ti tabler-file-text icon-32px text-muted d-block mb-3"></i>
          <h6 class="mb-2">{{ __('Shared documents') }}</h6>
          <p class="text-muted small mb-0">{{ __('View and upload documents.') }}</p>
        </div>
      </a>
    </div>
    <div class="col-md-6 col-lg-4 mb-4">
      <a href="{{ route('portal.messages.index') }}" class="card h-100 text-decoration-none text-body">
        <div class="card-body text-center py-5">
          <i class="icon-base ti tabler-message icon-32px text-muted d-block mb-3"></i>
          <h6 class="mb-2">{{ __('Messages') }}</h6>
          <p class="text-muted small mb-0">{{ __('Secure messaging with the office.') }}</p>
        </div>
      </a>
    </div>
  </div>
@endsection
