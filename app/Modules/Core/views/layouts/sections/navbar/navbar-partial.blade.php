@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
  <a href="{{ url('/') }}" class="app-brand-link">
    <span class="app-brand-logo demo">@include('core::_partials.macros')</span>
  </a>

  <!-- Display menu close icon only for horizontal-menu with navbar-full -->
  @if (isset($menuHorizontal))
  <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
    @include('core::_partials.navbar-icons', ['name' => 'x', 'class' => 'icon-sm'])
  </a>
  @endif
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
<div
  class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    @include('core::_partials.navbar-icons', ['name' => 'menu-2', 'class' => 'icon-md'])
  </a>
</div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  <!-- Search (Vuexy detached search: opens overlay on click / Ctrl+K) -->
  <div class="navbar-nav align-items-center search-toggler d-none d-lg-flex">
    <div class="nav-item search-box my-auto w-100">
      <div id="autocomplete" class="position-relative w-100"></div>
    </div>
  </div>
  @php
  $tenantUser = Auth::guard('admin')->check() ? null : Auth::user();
  $tenant = $tenantUser?->tenant;
  $showPublicSiteLink = $tenant && $tenant->getSettingsOrCreate()->hasPublicSiteEnabled();
@endphp
<ul class="navbar-nav flex-row align-items-center ms-auto">
    <!-- Language -->
    @php $currentLocale = app()->getLocale(); $locales = config('localization.supported', ['en' => 'English', 'ar' => 'العربية']); @endphp
    <li class="nav-item navbar-dropdown dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="{{ __('Language') }}" title="{{ $locales[$currentLocale] ?? $currentLocale }}">
        @include('core::_partials.navbar-icons', ['name' => 'language', 'class' => 'icon-md'])
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        @foreach($locales as $code => $label)
          <li>
            <a class="dropdown-item {{ $currentLocale === $code ? 'active' : '' }}" href="{{ url('/lang/' . $code) }}">
              {{ $label }}
            </a>
          </li>
        @endforeach
      </ul>
    </li>
    <!-- Theme -->
    <li class="nav-item navbar-dropdown dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow p-0" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Toggle theme">
        @include('core::_partials.navbar-icons', ['name' => 'sun', 'class' => 'icon-md theme-icon-active'])
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme">
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="light">
            @include('core::_partials.navbar-icons', ['name' => 'sun', 'class' => 'icon-22px me-3']) Light
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark">
            @include('core::_partials.navbar-icons', ['name' => 'moon', 'class' => 'icon-22px me-3']) Dark
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system">
            <span class="navbar-icon-svg d-inline-block me-3" style="width:22px;height:22px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="100%" height="100%"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22h6M12 18v4"/></svg></span> System
          </button>
        </li>
      </ul>
    </li>
    <!-- Apps (grid) -->
    <li class="nav-item navbar-dropdown dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Apps">
        @include('core::_partials.navbar-icons', ['name' => 'apps', 'class' => 'icon-md'])
      </a>
      <ul class="dropdown-menu dropdown-menu-end p-0 mt-2">
        <li class="dropdown-apps-list row g-0 py-2">
          <div class="col-4 text-center py-2"><a href="javascript:void(0);" class="dropdown-apps-item">@include('core::_partials.navbar-icons', ['name' => 'search', 'class' => 'icon-lg mb-1'])<span class="d-block small">Home</span></a></div>
          <div class="col-4 text-center py-2"><a href="javascript:void(0);" class="dropdown-apps-item">@include('core::_partials.navbar-icons', ['name' => 'bell', 'class' => 'icon-lg mb-1'])<span class="d-block small">Email</span></a></div>
          <div class="col-4 text-center py-2"><a href="javascript:void(0);" class="dropdown-apps-item">@include('core::_partials.navbar-icons', ['name' => 'apps', 'class' => 'icon-lg mb-1'])<span class="d-block small">Calendar</span></a></div>
        </li>
      </ul>
    </li>
    @if ($showPublicSiteLink)
    <!-- Visit My Website -->
    <li class="nav-item me-2 me-xl-0">
      <a class="nav-link hide-arrow p-0" href="{{ Helper::tenantPublicUrl($tenant) }}" target="_blank" rel="noopener" aria-label="{{ __('Visit My Website') }}" title="{{ __('Visit My Website') }}">
        @include('core::_partials.navbar-icons', ['name' => 'world', 'class' => 'icon-md'])
      </a>
    </li>
    @endif
    <!-- Notifications -->
    <li class="nav-item navbar-dropdown dropdown-notifications dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow p-0 position-relative" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Notifications">
        @include('core::_partials.navbar-icons', ['name' => 'bell', 'class' => 'icon-md'])
        <span class="badge rounded-pill bg-danger position-absolute top-0 end-0 translate-middle-y me-1">5</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end py-0 mt-2">
        <li class="dropdown-menu-header border-bottom">
          <div class="dropdown-header d-flex align-items-center py-3">
            <h6 class="mb-0 me-auto">Notifications</h6>
            <span class="badge bg-label-primary rounded-pill">5 New</span>
          </div>
        </li>
        <li class="dropdown-notifications-list scrollable-area">
          <ul class="list-group list-group-flush">
            <li class="list-group-item list-group-item-action dropdown-notifications-item">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3"><span class="avatar avatar-online">@include('core::_partials.navbar-icons', ['name' => 'bell', 'class' => 'icon-lg'])</span></div>
                <div class="flex-grow-1"><h6 class="mb-0">Notification title</h6><small class="text-body">Notification content</small></div>
              </div>
            </li>
          </ul>
        </li>
        <li class="dropdown-menu-footer border-top"><a class="dropdown-item d-flex justify-content-center py-2" href="javascript:void(0);">View all notifications</a></li>
      </ul>
    </li>
    <!-- User (أدمن من جدول admins أو يوزر تيننت من جدول users) -->
    @php
      $currentUser = Auth::guard('admin')->user() ?? Auth::user();
      $logoutRoute = Auth::guard('admin')->check() ? route('admin.logout') : route('logout');
    @endphp
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          @if ($currentUser && isset($currentUser->profile_photo_url) && $currentUser->profile_photo_url)
          <img src="{{ $currentUser->profile_photo_url }}" alt="{{ $currentUser->name }}" class="rounded-circle" />
          @else
          <span class="avatar-initial rounded-circle bg-label-primary">{{ $currentUser ? strtoupper(mb_substr($currentUser->name ?? 'U', 0, 1)) : 'U' }}</span>
          @endif
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item mt-0"
            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  @if ($currentUser && isset($currentUser->profile_photo_url) && $currentUser->profile_photo_url)
                  <img src="{{ $currentUser->profile_photo_url }}" alt="{{ $currentUser->name }}" class="rounded-circle" />
                  @else
                  <span class="avatar-initial rounded-circle bg-label-primary">{{ $currentUser ? strtoupper(mb_substr($currentUser->name ?? 'U', 0, 1)) : 'U' }}</span>
                  @endif
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ $currentUser->name ?? 'User' }}</h6>
                <small class="text-body-secondary">{{ Auth::guard('admin')->check() ? __('Admin') : __('Office') }}</small>
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <a class="dropdown-item"
            href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
            <i class="icon-base ti tabler-user me-3 icon-md"></i><span class="align-middle">My Profile</span> </a>
        </li>
        @if ($showPublicSiteLink)
        <li>
          <a class="dropdown-item" href="{{ Helper::tenantPublicUrl($tenant) }}" target="_blank" rel="noopener">
            <i class="icon-base ti tabler-world me-3 icon-md"></i><span class="align-middle">{{ __('Visit My Website') }}</span>
          </a>
        </li>
        @endif
        <li>
          <a class="dropdown-item" href="javascript:void(0);">
            <span class="d-flex align-items-center align-middle">
              <i class="flex-shrink-0 icon-base ti tabler-file-dollar me-3 icon-md"></i><span
                class="flex-grow-1 align-middle">Billing</span>
              <span class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center">4</span>
            </span>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        @if ($currentUser)
        <li>
          <a class="dropdown-item" href="{{ $logoutRoute }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="icon-base ti tabler-logout icon-md me-3"></i><span>Logout</span>
          </a>
        </li>
        <form method="POST" id="logout-form" action="{{ $logoutRoute }}">
          @csrf
        </form>
        @else
        <li>
          <div class="d-grid px-2 pt-2 pb-1">
            <a class="btn btn-sm btn-danger d-flex"
              href="{{ route('admin.login') }}">
              <small class="align-middle">Login</small>
              <i class="icon-base ti tabler-login ms-2 icon-14px"></i>
            </a>
          </div>
        </li>
        @endif
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>
