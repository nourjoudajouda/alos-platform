{{-- صفحة رئيسية بسيطة — placeholder حتى اختيار وتركيب القالب --}}
@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('core::layouts.layoutMaster')

@section('title', __('ALOS - Legal Office Management'))

@section('page-style')
<style>
  .landing-simple { min-height: 50vh; display: flex; align-items: center; justify-content: center; padding: 4rem 0; }
</style>
@endsection

@section('content')
<div class="landing-simple">
  <div class="container text-center">
    <h1 class="mb-3 fw-bold">{{ isset($tenant) ? $tenant->name : config('variables.templateName') }}</h1>
    <p class="text-muted mb-4">{{ __('Legal office management') }}</p>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
      <a href="{{ url('/login') }}" class="btn btn-outline-primary">{{ __('Log in') }}</a>
      <a href="{{ url('/register') }}" class="btn btn-primary">{{ __('Get started') }}</a>
    </div>
  </div>
</div>
@endsection
