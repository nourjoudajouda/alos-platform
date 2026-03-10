{{-- ALOS-S1-20 / ALOS-S1-21 — Navbar للموقع الخارجي — يستخدم tenant_settings --}}
<nav class="layout-navbar shadow-sm py-3 border-bottom navbar navbar-expand-lg bg-body">
  <div class="container">
    <a class="navbar-brand fw-bold text-body text-decoration-none" href="{{ url('/' . $tenant->slug) }}">
      @if (isset($settings) && $settings->logo_url)
        <img src="{{ $settings->logo_url }}" alt="{{ $settings->getDisplayName() }}" height="36" class="d-inline-block align-text-top me-2">
      @else
        <i class="icon-base ti tabler-building-store me-2"></i>
      @endif
      {{ isset($settings) ? $settings->getDisplayName() : $tenant->name }}
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#tenantPublicNav" aria-controls="tenantPublicNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="tenantPublicNav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        @php $p = isset($settings) && $settings->primary_color ? '#' . ltrim($settings->primary_color, '#') : null; @endphp
        <li class="nav-item"><a class="btn btn-outline-primary btn-sm" href="{{ url('/portal/login') }}" @if($p) style="border-color:{{ $p }};color:{{ $p }}" @endif>{{ __('Log in') }}</a></li>
        <li class="nav-item"><a class="btn btn-primary btn-sm" href="{{ url('/portal/login') }}" @if($p) style="background-color:{{ $p }};border-color:{{ $p }}" @endif>{{ __('Get started') }}</a></li>
      </ul>
    </div>
  </div>
</nav>
