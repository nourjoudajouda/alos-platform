@php
  use Illuminate\Support\Facades\Route;
  $configData = $configData ?? Helper::appClasses();
  $currentRouteName = Route::currentRouteName();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu" @foreach ($configData['menuAttributes'] ?? [] as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>
  <div class="app-brand demo">
    <a href="{{ route('portal.dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo demo">@include('core::_partials.macros')</span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base menu-toggle-icon d-none d-xl-block"></i>
      <i class="icon-base ti tabler-x d-block d-xl-none"></i>
    </a>
  </div>
  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner py-1">
    <li class="menu-header small">
      <span class="menu-header-text">{{ __('Client Portal') }}</span>
    </li>
    <li class="menu-item {{ $currentRouteName === 'portal.dashboard' ? 'active' : '' }}">
      <a href="{{ route('portal.dashboard') }}" class="menu-link">
        <i class="icon-base ti tabler-smart-home"></i>
        <div>{{ __('Dashboard') }}</div>
      </a>
    </li>
  </ul>
</aside>
