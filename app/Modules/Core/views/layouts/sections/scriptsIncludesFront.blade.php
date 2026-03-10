@php
  $primaryColor = isset($_COOKIE['front-primaryColor']) ? $_COOKIE['front-primaryColor'] : ($configData['color'] ?? null);
@endphp
<!-- Head scripts (public assets, no Vite) -->
<script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
@if ($configData['hasCustomizer'] ?? true)
  <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>
@endif
<script src="{{ asset('assets/js/front-config.js') }}"></script>

@if ($configData['hasCustomizer'] ?? true)
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.TemplateCustomizer) {
      try {
        window.templateCustomizer = new TemplateCustomizer({
          defaultTextDir: "{{ $configData['textDirection'] ?? 'ltr' }}",
          @if ($primaryColor) defaultPrimaryColor: "{{ $primaryColor }}", @endif
          defaultTheme: "{{ $configData['themeOpt'] ?? 'light' }}",
          defaultShowDropdownOnHover: "{{ $configData['showDropdownOnHover'] ?? 'true' }}",
          displayCustomizer: "{{ $configData['displayCustomizer'] ?? 'true' }}",
          lang: '{{ app()->getLocale() }}',
          'controls': <?php echo json_encode(['color', 'theme', 'rtl']); ?>,
        });
        @if ($primaryColor)
        if (window.Helpers && typeof window.Helpers.setColor === 'function') {
          window.Helpers.setColor("{{ $primaryColor }}", true);
        }
        @endif
      } catch (error) {
        console.warn('Template customizer initialization error:', error);
      }
    }
  });
</script>
@endif
