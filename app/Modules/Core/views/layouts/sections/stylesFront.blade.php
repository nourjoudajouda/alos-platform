<!-- BEGIN: Theme CSS (public assets, no Vite) -->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
  href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap"
  rel="stylesheet" />

<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />

@if ($configData['hasCustomizer'] ?? true)
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
@endif

<!-- Vendor Styles -->
@yield('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/front-page.css') }}" />

<!-- Page Styles -->
@yield('page-style')

<!-- ALOS Public: لون أساسي #0D9394 (أزرق/تركواز هادئ) -->
<style>
  html[data-template*="front"] {
    --bs-primary: #0D9394;
    --bs-primary-rgb: 13, 147, 148;
    --bs-primary-bg-subtle: rgba(13, 147, 148, 0.1);
    --bs-primary-border-subtle: rgba(13, 147, 148, 0.3);
    --bs-primary-contrast: #fff;
  }
  html[data-template*="front"] .landing-footer .footer-top { background-color: #0D9394 !important; }
</style>
