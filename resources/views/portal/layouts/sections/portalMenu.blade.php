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
    <li class="menu-item {{ str_starts_with($currentRouteName ?? '', 'portal.cases') ? 'active' : '' }}">
      <a href="{{ route('portal.cases.index') }}" class="menu-link">
        <i class="icon-base ti tabler-briefcase"></i>
        <div>{{ __('My Cases') }}</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($currentRouteName ?? '', 'portal.messages') ? 'active' : '' }}">
      <a href="{{ route('portal.messages.index') }}" class="menu-link">
        <i class="icon-base ti tabler-message"></i>
        <div>{{ __('Messages') }}</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($currentRouteName ?? '', 'portal.consultations') ? 'active' : '' }}">
      <a href="{{ route('portal.consultations.index') }}" class="menu-link">
        <i class="icon-base ti tabler-calendar-event"></i>
        <div>{{ __('Consultations') }}</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($currentRouteName ?? '', 'portal.documents') ? 'active' : '' }}">
      <a href="{{ route('portal.documents.index') }}" class="menu-link">
        <i class="icon-base ti tabler-file-text"></i>
        <div>{{ __('Documents') }}</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($currentRouteName ?? '', 'portal.reports') ? 'active' : '' }}">
      <a href="{{ route('portal.reports.index') }}" class="menu-link">
        <i class="icon-base ti tabler-file-report"></i>
        <div>{{ __('Reports') }}</div>
      </a>
    </li>
    <li class="menu-item {{ str_starts_with($currentRouteName ?? '', 'portal.notifications') ? 'active' : '' }}">
      <a href="{{ route('portal.notifications.index') }}" class="menu-link">
        <i class="icon-base ti tabler-bell-ringing"></i>
        <div>{{ __('Notifications') }}</div>
      </a>
    </li>
  </ul>
</aside>
