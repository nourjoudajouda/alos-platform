@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $containerNav = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid';
  $container = $containerNav;
  $navbarDetached = 'navbar-detached';
@endphp

@extends('core::layouts.commonMaster')

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      @include('portal::layouts.sections.portalMenu')

      <div class="layout-page">
        <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme" id="layout-navbar">
          @include('portal::layouts.sections.portalNavbar')
        </nav>

        <div class="content-wrapper">
          <div class="{{ $container }} flex-grow-1 container-p-y" dir="{{ $contentDir }}">
            @if (session('success'))
              <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif
            @yield('content')
          </div>
          @php $containerFooter = $configData['contentLayout'] === 'compact' ? 'container-xxl' : 'container-fluid'; @endphp
          <footer class="content-footer footer bg-footer-theme">
            <div class="{{ $containerFooter }}">
              <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                <div class="text-body">
                  &#169; <script>document.write(new Date().getFullYear());</script>, made with ❤️ by <a href="{{ config('variables.creatorUrl', '#') }}" target="_blank" class="footer-link">{{ config('variables.creatorName', 'ALOS') }}</a>
                </div>
              </div>
            </div>
          </footer>
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
  </div>
  <div class="layout-overlay layout-menu-toggle"></div>
  <div class="drag-target"></div>
@endsection
