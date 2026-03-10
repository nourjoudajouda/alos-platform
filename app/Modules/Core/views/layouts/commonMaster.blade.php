<!DOCTYPE html>
@php
  use Illuminate\Support\Str;
  use App\Helpers\Helpers;

  $isFrontLayout = in_array($configData['layout'] ?? '', ['front', 'tenant-public'], true);
  $menuFixed =
      $configData['layout'] === 'vertical'
          ? $menuFixed ?? ''
          : ($isFrontLayout
              ? ''
              : $configData['headerType']);
  $navbarType =
      $configData['layout'] === 'vertical'
          ? $configData['navbarType']
          : ($isFrontLayout
              ? 'layout-navbar-fixed'
              : '');
  $isFront = ($isFront ?? '') == true ? 'Front' : '';
  $contentLayout = isset($container) ? ($container === 'container-xxl' ? 'layout-compact' : 'layout-wide') : '';

  // Get skin name from configData - only applies to admin layouts
  $isAdminLayout = !in_array($configData['layout'] ?? '', ['front', 'tenant-public'], true);
  $skinName = $isAdminLayout ? $configData['skinName'] ?? 'default' : 'default';

  // Get semiDark value from configData - only applies to admin layouts
  $semiDarkEnabled = $isAdminLayout && filter_var($configData['semiDark'] ?? false, FILTER_VALIDATE_BOOLEAN);

  // Generate primary color CSS if color is set
  $primaryColorCSS = '';
  if (isset($configData['color']) && $configData['color']) {
      $primaryColorCSS = Helpers::generatePrimaryColorCSS($configData['color']);
  }

@endphp

<html lang="{{ session()->get('locale') ?? app()->getLocale() }}"
  class="{{ $navbarType ?? '' }} {{ $contentLayout ?? '' }} {{ $menuFixed ?? '' }} {{ $menuCollapsed ?? '' }} {{ $footerFixed ?? '' }} {{ $customizerHidden ?? '' }}"
  dir="{{ $configData['textDirection'] }}" data-skin="{{ $skinName }}" data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{ url('/') }}" data-framework="laravel" data-template="{{ $configData['layout'] }}-menu-template"
  data-bs-theme="{{ $configData['theme'] }}" @if ($isAdminLayout && $semiDarkEnabled) data-semidark-menu="true" @endif>

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>
    @yield('title') | {{ config('variables.templateName') ? config('variables.templateName') : 'TemplateName' }}
    - {{ config('variables.templateSuffix') ? config('variables.templateSuffix') : 'TemplateSuffix' }}
  </title>
  <meta name="description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords"
    content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}" />
  <meta property="og:title" content="{{ config('variables.ogTitle') ? config('variables.ogTitle') : '' }}" />
  <meta property="og:type" content="{{ config('variables.ogType') ? config('variables.ogType') : '' }}" />
  <meta property="og:url" content="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <meta property="og:image" content="{{ config('variables.ogImage') ? config('variables.ogImage') : '' }}" />
  <meta property="og:description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta property="og:site_name"
    content="{{ config('variables.creatorName') ? config('variables.creatorName') : '' }}" />
  <meta name="robots" content="noindex, nofollow" />
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}" />
  <!-- Favicon -->
  @hasSection('favicon')
    @yield('favicon')
  @else
  <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon/favicon.png') }}" />
  @endif

  <!-- Include Styles -->
  <!-- $isFront is used to append the front layout styles only on the front layout otherwise the variable will be blank -->
  @include('core::layouts.sections.styles' . $isFront)

  <!-- ALOS logo: light/dark by theme -->
  <style>
    [data-bs-theme="light"] .alos-logo-light,
    html:not([data-bs-theme="dark"]) .alos-logo-light { display: block !important; }
    [data-bs-theme="light"] .alos-logo-dark,
    html:not([data-bs-theme="dark"]) .alos-logo-dark { display: none !important; }
    [data-bs-theme="dark"] .alos-logo-light { display: none !important; }
    [data-bs-theme="dark"] .alos-logo-dark { display: block !important; }
  </style>
  <!-- Cairo font for Arabic — كل النظام باللغة العربية -->
  <style>
    html[lang="ar"] { --bs-font-sans-serif: "Cairo", "Public Sans", -apple-system, blinkmacsystemfont, "Segoe UI", sans-serif; }
  </style>
  <!-- Sidebar: لون أيقونات القائمة = لون الثيم، وفصل المجموعات -->
  <style>
    .layout-menu .menu-inner > .menu-item > .menu-link .menu-icon,
    .layout-menu .menu-inner > .menu-item > .menu-link i.menu-icon { color: var(--bs-primary) !important; }
    .layout-menu .menu-inner > .menu-item.active > .menu-link .menu-icon,
    .layout-menu .menu-inner > .menu-item.active > .menu-link i.menu-icon { color: var(--bs-primary-contrast, #fff) !important; }
    .layout-menu .menu-inner > .menu-header:not(:first-child) {
      margin-top: 1rem;
      padding-top: 0.75rem;
      border-top: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.1));
    }
  </style>

  @if (
      $primaryColorCSS &&
          (config('custom.custom.primaryColor') ||
              isset($_COOKIE['admin-primaryColor']) ||
              isset($_COOKIE['front-primaryColor'])))
    <!-- Primary Color Style -->
    <style id="primary-color-style">
      {!! $primaryColorCSS !!}
    </style>
  @endif

  <!-- Include Scripts for customizer, helper, analytics, config -->
  <!-- $isFront is used to append the front layout scriptsIncludes only on the front layout otherwise the variable will be blank -->
  @include('core::layouts.sections.scriptsIncludes' . $isFront)
</head>

<body>
  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->

  {{-- remove while creating package --}}
  {{-- remove while creating package end --}}

  <!-- Include Scripts -->
  <!-- $isFront is used to append the front layout scripts only on the front layout otherwise the variable will be blank -->
  @include('core::layouts.sections.scripts' . $isFront)
</body>

</html>
