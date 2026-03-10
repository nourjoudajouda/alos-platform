@php
  $configData = Helper::appClasses();
  $isFront = true;
  $customizerHidden = (config('custom.custom.customizerHide') ?? false) ? 'customizer-hide' : '';
@endphp

@section('layoutContent')
  @extends('core::layouts.commonMaster')

  @include('core::layouts.sections.navbar.navbar-front')

  <!-- Sections:Start -->
  @yield('content')
  <!-- / Sections:End -->

  @include('core::layouts.sections.footer.footer-front')
@endsection
