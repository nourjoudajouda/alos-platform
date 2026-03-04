@php
  $height = $height ?? '60';
@endphp
<span class="app-brand-logo-wrapper d-inline-block" style="height: {{ $height }}px;">
  <img src="{{ asset('assets/img/logo/alos-logo-light.png') }}" alt="ALOS" class="app-brand-logo-img alos-logo-light" style="height: 60px; width: auto; max-width: 140px; display: block;" />
  <img src="{{ asset('assets/img/logo/alos-logo-dark.png') }}" alt="ALOS" class="app-brand-logo-img alos-logo-dark" style="height: 60px; width: auto; max-width: 140px; display: none;" />
</span>
