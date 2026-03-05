@php
  use Illuminate\Support\Facades\Auth;
  $containerNav = ($configData['contentLayout'] ?? '') === 'compact' ? 'container-xxl' : 'container-fluid';
@endphp

<div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    @include('core::_partials.navbar-icons', ['name' => 'menu-2', 'class' => 'icon-md'])
  </a>
</div>

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  <ul class="navbar-nav flex-row align-items-center ms-auto">
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(mb_substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <div class="dropdown-item mt-0 pt-2 pb-2">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(mb_substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                <small class="text-body-secondary">{{ __('Client Portal') }}</small>
              </div>
            </div>
          </div>
        </li>
        <li><div class="dropdown-divider my-1 mx-n2"></div></li>
        <li>
          <a class="dropdown-item" href="{{ route('portal.logout') }}" onclick="event.preventDefault(); document.getElementById('portal-logout-form').submit();">
            <i class="icon-base ti tabler-logout icon-md me-3"></i><span>{{ __('Logout') }}</span>
          </a>
        </li>
        <form method="POST" id="portal-logout-form" action="{{ route('portal.logout') }}" class="d-none">@csrf</form>
      </ul>
    </li>
  </ul>
</div>
