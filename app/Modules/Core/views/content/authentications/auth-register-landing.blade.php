@extends('core::layouts.landing')

@section('title', __('Create your account') . ' — ALOS')

@push('styles')
<style>
/* تطبيق ستايل Procounsel على كل حقول النموذج */
.login-page__form-input-box input,
.login-page__form-input-box input[type=text],
.login-page__form-input-box input[type=email],
.login-page__form-input-box input[type=password] {
  height: 60px;
  width: 100%;
  border: 1px solid var(--procounsel-border-color, #d9d9d9);
  background-color: transparent;
  padding-left: 30px;
  padding-right: 30px;
  outline: none;
  font-size: 15px;
  color: var(--procounsel-text, #838790);
  display: block;
  font-weight: 400;
}
.login-page__form-input-box input::placeholder {
  color: var(--procounsel-text, #838790);
  opacity: 0.8;
}
.login-page__form-input-box .form-label {
  display: block;
  margin-bottom: 8px;
  font-size: 14px;
  color: var(--procounsel-text, #838790);
}
.login-page__form-input-box .form-text {
  margin-top: 6px;
  font-size: 13px;
}
</style>
@endpush

@section('content')
@php $landing = asset('landing'); @endphp
<section class="page-header">
    <div class="page-header__bg"></div>
    <div class="container">
        <h2 class="page-header__title bw-split-in-right">{{ __('Create your account') }}</h2>
        <ul class="procounsel-breadcrumb list-unstyled">
            <li><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
            <li><span>{{ __('Register') }}</span></li>
        </ul>
    </div>
</section>

<section class="login-page">
    <div class="container">
        {{-- ALOS branding: نفس هوية الموقع الرئيسي --}}
        <div class="text-center mb-5 wow fadeInUp" data-wow-delay="100ms">
            <a href="{{ url('/') }}" class="d-inline-block mb-3">
                @if(file_exists(public_path('landing/assets/images/logo-dark-2.png')))
                    <img src="{{ asset('landing/assets/images/logo-dark-2.png') }}" alt="ALOS" width="140" />
                @else
                    <img src="{{ $landing }}/assets/images/logo-dark.png" alt="ALOS" width="140" onerror="this.style.display='none'" />
                @endif
            </a>
            <h4 class="mb-1">ALOS — {{ __('Legal Office Management') }}</h4>
            <p class="text-muted small mb-0">{{ __('Create a new tenant and start managing your office.') }}</p>
        </div>

        <div class="login-page__wrap">
            <div class="row justify-content-center">
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="300ms">
                    <form class="login-page__form" action="{{ route('register.store') }}" method="POST" id="formRegister">
                        @csrf
                        <h3 class="login-page__wrap__title">{{ __('New tenant registration') }}</h3>
                        <p class="mb-4 text-muted">{{ __('Fill in the tenant details. You will be the first admin user (managing partner) for this tenant.') }}</p>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        @endif
                        {{-- حقول Tenant: كل اثنين جنب بعض --}}
                        <div class="row mb-0">
                            <div class="col-md-6 mb-3">
                                <div class="login-page__form-input-box">
                                    <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="{{ __('Subdomain') }} * (e.g. my-office)" required autocomplete="username" />
                                    <div class="form-text">{{ __('Letters, numbers, dash and underscore only.') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="login-page__form-input-box">
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="{{ __('Tenant name') }} *" required />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 mb-3">
                                <div class="login-page__form-input-box">
                                    <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" placeholder="{{ __('Managing partner') }} *" required />
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="login-page__form-input-box">
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="{{ __('Email') }} *" required autocomplete="email" />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-6 mb-3">
                                <div class="login-page__form-input-box">
                                    <input type="password" name="password" id="password" placeholder="{{ __('Password') }} *" required autocomplete="new-password" />
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="login-page__form-input-box">
                                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="{{ __('Confirm Password') }} *" required autocomplete="new-password" />
                                </div>
                            </div>
                        </div>
                        <p class="small text-muted mb-3">{{ __('Status, user limit, lawyer limit, storage limit, start date and end date are set automatically for the new tenant.') }}</p>
                        <div class="login-page__checked-box">
                            <input type="checkbox" name="terms" id="terms" value="1" />
                            <label for="terms"><span></span>{{ __('I agree to privacy policy & terms') }}</label>
                        </div>
                        <div class="login-page__form-btn-box">
                            <button type="submit" class="procounsel-btn"><i>{{ __('Sign up') }}</i><span>{{ __('Sign up') }}</span></button>
                        </div>
                    </form>
                    <p class="text-center mt-4">
                        <span>{{ __('Already have an account?') }}</span>
                        <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
