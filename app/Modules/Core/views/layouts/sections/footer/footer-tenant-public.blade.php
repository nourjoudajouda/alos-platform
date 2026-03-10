{{-- ALOS-S1-20 — Footer للموقع الخارجي للتيننت --}}
<footer class="tenant-public-footer py-4 border-top bg-body-secondary mt-auto">
  <div class="container">
    <div class="row align-items-center justify-content-between">
      <div class="col-auto">
        <span class="text-muted small">© <script>document.write(new Date().getFullYear());</script> {{ isset($settings) ? $settings->getDisplayName() : $tenant->name }}. {{ __('All rights reserved.') }}</span>
      </div>
      <div class="col-auto">
        <a href="{{ url('/portal/login') }}" class="btn btn-outline-primary btn-sm">{{ __('Log in') }}</a>
      </div>
    </div>
  </div>
</footer>
