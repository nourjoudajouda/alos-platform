@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('core::layouts.layoutMaster')

@section('title', __('Create your account') . ' — ALOS')

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
      <!-- Register Card -->
      <div class="card">
        <div class="card-body">
          <!-- ALOS Logo & Branding -->
          <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="d-inline-block">
              @if(file_exists(public_path('landing/assets/images/logo-dark-2.png')))
                <img src="{{ asset('landing/assets/images/logo-dark-2.png') }}" alt="ALOS" width="160" class="mb-2" onerror="this.style.display='none'" />
              @else
                <span class="app-brand-logo demo">@include('core::_partials.macros')</span>
              @endif
            </a>
            <h4 class="mb-1 mt-2">ALOS — {{ __('Legal Office Management') }}</h4>
            <p class="text-muted small mb-0">{{ __('Create your tenant account and start managing your office in one place.') }}</p>
          </div>
          <!-- /Logo -->
          <h4 class="mb-1">{{ __('Create your account') }}</h4>
          <p class="mb-6">{{ __('Register your office (tenant) to get started. You will be the first admin user.') }}</p>

          <form id="formAuthentication" class="mb-6" action="{{ route('register.store') }}" method="POST">
            @csrf
            <div class="mb-3 form-control-validation">
              <label for="username" class="form-label">{{ __('Username') }}</label>
              <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" placeholder="{{ __('e.g. myoffice or law-firm-xyz') }}" required />
              <div class="form-text">{{ __('Unique. Letters, numbers, dash and underscore only. Your domain will be created from this.') }}</div>
              @error('username')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 form-control-validation">
              <label for="name" class="form-label">{{ __('Office/Company name') }}</label>
              <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="{{ __('e.g. Law Office XYZ') }}" required />
              @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 form-control-validation">
              <label for="admin_name" class="form-label">{{ __('Managing partner name') }}</label>
              <input type="text" class="form-control" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" placeholder="{{ __('Your full name') }}" required />
              <div class="form-text">{{ __('First admin user for this tenant (office).') }}</div>
              @error('admin_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 form-control-validation">
              <label for="email" class="form-label">{{ __('Email') }}</label>
              <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Enter your email') }}" required />
              @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 form-password-toggle form-control-validation">
              <label class="form-label" for="password">{{ __('Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
              @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 form-control-validation">
              <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
              <div class="input-group input-group-merge">
                <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>
            <div class="my-4 form-control-validation">
              <div class="form-check mb-0 ms-2">
                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" value="1" />
                <label class="form-check-label" for="terms-conditions">{{ __('I agree to privacy policy & terms') }}</label>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Sign up') }}</button>
          </form>

          <p class="text-center">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}">
              <span>Sign in instead</span>
            </a>
          </p>

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
        </div>
      </div>
      <!-- Register Card -->
    </div>
  </div>
</div>
@endsection
