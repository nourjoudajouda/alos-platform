<!-- BEGIN: Vendor JS (public assets, no Vite) -->
<script src="{{ asset('assets/vendor/js/dropdown-hover.js') }}"></script>
<script src="{{ asset('assets/vendor/js/mega-dropdown.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>

@if ($configData['hasCustomizer'] ?? true)
  <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>
@endif

@yield('vendor-script')
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS -->
<script src="{{ asset('assets/js/front-main.js') }}"></script>
<!-- END: Theme JS -->

@stack('pricing-script')

<!-- BEGIN: Page JS -->
@yield('page-script')
<!-- END: Page JS -->
