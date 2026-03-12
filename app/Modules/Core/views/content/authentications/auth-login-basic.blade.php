@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('core::layouts.layoutMaster')

@section('title', __('Login') . ' — ' . ($systemName ?? config('app.name')))

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
      <!-- Login -->
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ isset($isAdminLogin) && $isAdminLogin ? route('admin.login') : route('home') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                @if(!empty($systemLogoUrl))
                  <span class="app-brand-logo-wrapper d-inline-block" style="height: 60px;">
                    <img src="{{ $systemLogoUrl }}" alt="{{ $systemName ?? config('app.name') }}" class="app-brand-logo-img" style="height: 60px; width: auto; max-width: 140px; object-fit: contain;" />
                  </span>
                @else
                  @include('core::_partials.macros')
                @endif
              </span>
            </a>
          </div>
          <!-- /Logo -->
          <h4 class="mb-1">{{ __('Welcome back') }} 👋</h4>
          <p class="mb-6">{{ isset($isAdminLogin) && $isAdminLogin ? __('Sign in to the admin panel.') : __('Sign in to your account to access the dashboard.') }}</p>

          <form id="formAuthentication" class="mb-4" action="{{ isset($isAdminLogin) && $isAdminLogin ? route('admin.login.store') : route('login.store') }}" method="POST">
            @csrf
            <div class="mb-6 form-control-validation">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email" autofocus required />
              @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-6 form-password-toggle form-control-validation">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password"
                  placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>
            <div class="my-8">
              <div class="d-flex justify-content-between">
                <div class="form-check mb-0 ms-2">
                  <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" />
                  <label class="form-check-label" for="remember-me"> Remember Me </label>
                </div>
                <a href="javascript:void(0);">
                  <p class="mb-0">Forgot Password?</p>
                </a>
              </div>
            </div>
            <div class="mb-6">
              <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
            </div>
          </form>

          <p class="text-center">
            @if (isset($isAdminLogin) && $isAdminLogin)
              <a href="{{ route('home') }}">{{ __('Back to site') }}</a>
            @else
              <span>{{ __('New on our platform?') }}</span>
              <a href="{{ route('register') }}"><span>{{ __('Create an account') }}</span></a>
            @endif
          </p>

          @if (empty($isAdminLogin))
          <div class="divider my-6">
            <div class="divider-text">or</div>
          </div>

          <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-facebook me-1_5">
              <i class="icon-base ti tabler-brand-facebook-filled icon-20px"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-twitter me-1_5">
              <i class="icon-base ti tabler-brand-twitter-filled icon-20px"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-github me-1_5">
              <i class="icon-base ti tabler-brand-github-filled icon-20px"></i>
            </a>

            <a href="javascript:;" class="btn btn-icon rounded-circle btn-text-google-plus">
              <i class="icon-base ti tabler-brand-google-filled icon-20px"></i>
            </a>
          </div>
          @endif
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
</div>
@endsection
