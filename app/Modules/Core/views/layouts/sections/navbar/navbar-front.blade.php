<!-- Navbar بسيط — placeholder حتى تركيب القالب -->
<nav class="layout-navbar shadow-none py-3 border-bottom bg-white">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <a href="{{ url('/') }}" class="text-decoration-none fw-bold fs-5 text-body">{{ config('variables.templateName') }}</a>
      <div class="d-flex align-items-center gap-2">
        <a href="{{ url('/login') }}" class="btn btn-outline-primary btn-sm">{{ __('Log in') }}</a>
        <a href="{{ url('/register') }}" class="btn btn-primary btn-sm">{{ __('Get started') }}</a>
      </div>
    </div>
  </div>
</nav>
