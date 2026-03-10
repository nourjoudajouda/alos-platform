@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.commonMaster')

@php
  $contentNavbar = $contentNavbar ?? true;
  $containerNav = $containerNav ?? 'container-xxl';
  $isNavbar = $isNavbar ?? true;
  $isMenu = $isMenu ?? true;
  $isFlex = $isFlex ?? false;
  $isFooter = $isFooter ?? true;
  $customizerHidden = $customizerHidden ?? '';
  $navbarDetached = 'navbar-detached';
  $menuFixed = $configData['menuFixed'] ?? '';
  $navbarType = $configData['navbarType'] ?? '';
  $footerFixed = $configData['footerFixed'] ?? '';
  $menuCollapsed = $configData['menuCollapsed'] ?? '';
  $container = (isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
@endphp

@section('layoutContent')
  <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
    <div class="layout-container">

      @if ($isMenu)
        @include('core::layouts.sections.menu.verticalMenuOffice')
      @endif

      <div class="layout-page">
        @if ($isNavbar)
          @include('core::layouts.sections.navbar.navbar')
        @endif
        <div class="content-wrapper">
          <div class="{{ $container }} flex-grow-1 container-p-y">
            @yield('content')
          </div>
          @if ($isFooter)
            @include('core::layouts.sections.footer.footer')
          @endif
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    @if ($isMenu)
      <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <div class="drag-target"></div>
  </div>
@endsection
