<!-- Footer بسيط — placeholder -->
<footer class="landing-footer footer-text">
  <div class="footer-top py-4" style="background-color: #0D9394;">
    <div class="container text-center">
      <a href="{{ route('home') }}" class="text-white fw-bold text-decoration-none">{{ config('variables.templateName') }}</a>
    </div>
  </div>
  <div class="footer-bottom py-3 border-top bg-body">
    <div class="container text-center small text-muted">
      <span>© <script>document.write(new Date().getFullYear());</script> {{ config('variables.creatorName') }}. {{ __('All rights reserved.') }}</span>
    </div>
  </div>
</footer>
<!-- Footer: End -->
