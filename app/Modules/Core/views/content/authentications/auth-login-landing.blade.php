@extends('core::layouts.landing')

@section('title', __('Login') . ' — ALOS')

@section('content')
@php $landing = asset('landing'); @endphp
<section class="page-header">
    <div class="page-header__bg"></div>
    <div class="container">
        <h2 class="page-header__title bw-split-in-right">{{ __('Login') }}</h2>
        <ul class="procounsel-breadcrumb list-unstyled">
            <li><a href="{{ url('/') }}">{{ __('Home') }}</a></li>
            <li><span>{{ __('Login') }}</span></li>
        </ul>
    </div>
</section>

<section class="login-page">
    <div class="container">
        <div class="text-center mb-5 wow fadeInUp" data-wow-delay="100ms">
            <a href="{{ url('/') }}" class="d-inline-block mb-3">
                @if(file_exists(public_path('landing/assets/images/logo-dark-2.png')))
                    <img src="{{ asset('landing/assets/images/logo-dark-2.png') }}" alt="ALOS" width="140" />
                @else
                    <img src="{{ $landing }}/assets/images/logo-dark.png" alt="ALOS" width="140" onerror="this.style.display='none'" />
                @endif
            </a>
            <h4 class="mb-1">ALOS — {{ __('Legal Office Management') }}</h4>
            <p class="text-muted small mb-0">{{ __('Sign in to your tenant account.') }}</p>
        </div>
        <div class="login-page__wrap">
            <div class="row justify-content-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="300ms">
                    <form class="login-page__form" action="{{ route('login.store') }}" method="POST">
                        @csrf
                        <h3 class="login-page__wrap__title">{{ __('Login to your account') }}</h3>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $err)
                                    <div>{{ $err }}</div>
                                @endforeach
                            </div>
                        @endif
                        <div class="login-page__form-input-box">
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Email address') }} *" required autofocus />
                        </div>
                        <div class="login-page__form-input-box">
                            <input type="password" name="password" placeholder="{{ __('Password') }} *" required />
                        </div>
                        <div class="login-page__checked-box">
                            <div class="login-page__checked">
                                <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                                <label for="remember"><span></span>{{ __('Remember Me?') }}</label>
                            </div>
                            <div class="login-page__form-forgot-password">
                                <a href="#">{{ __('Forgot your Password?') }}</a>
                            </div>
                        </div>
                        <div class="login-page__form-btn-box">
                            <button type="submit" class="procounsel-btn"><i>{{ __('Login') }}</i><span>{{ __('Login') }}</span></button>
                        </div>
                    </form>
                    <p class="text-center mt-4">
                        <span>{{ __('Don\'t have an account?') }}</span>
                        <a href="{{ route('register') }}">{{ __('Get started') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
