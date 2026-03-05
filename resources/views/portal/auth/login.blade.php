@php
  $pageConfigs = ['myLayout' => 'blank'];
  $customizerHidden = 'customizer-hide';
@endphp

@extends('core::layouts.layoutMaster')

@section('title', __('Client Portal') . ' — ' . config('app.name'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endsection

@section('page-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
  <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
@endsection

@section('content')
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-6">
        <div class="card">
          <div class="card-body">
            <div class="app-brand justify-content-center mb-6">
              <a href="{{ url('/') }}" class="app-brand-link">
                <span class="app-brand-logo demo">@include('core::_partials.macros')</span>
              </a>
            </div>
            <h4 class="mb-1">{{ __('Client Portal') }}</h4>
            <p class="mb-6">{{ __('Sign in to view your cases and messages.') }}</p>

            @if (session('message'))
              <div class="alert alert-info mb-4">{{ session('message') }}</div>
            @endif

            <form id="formPortalAuth" class="mb-4" action="{{ route('portal.login.store') }}" method="POST">
              @csrf
              <div class="mb-6 form-control-validation">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" class="form-control" id="email" name="email"
                  value="{{ old('email') }}"
                  placeholder="{{ __('Enter your email') }}" autofocus required />
                @error('email')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-6 form-password-toggle form-control-validation">
                <label class="form-label" for="password">{{ __('Password') }}</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                </div>
              </div>
              <div class="my-8">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" />
                  <label class="form-check-label" for="remember-me">{{ __('Remember Me') }}</label>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Sign in') }}</button>
              </div>
            </form>

            <p class="text-center mb-0">
              <a href="{{ route('login') }}">{{ __('Staff login') }}</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
