{{-- ALOS-S1-20 — Layout خاص بالمواقع الخارجية للتيننت — منفصل عن admin و client portal --}}
@php
  $configData = Helper::appClasses();
  $isFront = true;
  $customizerHidden = 'customizer-hide';
@endphp

@section('favicon')
  <link rel="icon" type="image/png" href="{{ isset($settings) && $settings->favicon_url ? $settings->favicon_url : asset('assets/img/favicon/favicon.png') }}" />
@endsection

@section('layoutContent')
  @extends('core::layouts.commonMaster')

  <div class="d-flex flex-column min-vh-100">
    @include('core::layouts.sections.navbar.navbar-tenant-public')

    <main class="flex-grow-1" style="margin-top: 100px;">
      @yield('content')
    </main>

    <hr class="my-0">
    @include('core::layouts.sections.footer.footer-tenant-public')
  </div>
@endsection
