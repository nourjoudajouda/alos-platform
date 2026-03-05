{{-- كرت إحصائي واحد للقوائم — استخدمه داخل @yield('crud_stats') --}}
@php
  $icon = $icon ?? 'ti tabler-chart-bar';
  $bgLabel = $bgLabel ?? 'primary';
@endphp
<div class="col-sm-6 col-xl-3">
  <div class="card h-100">
    <div class="card-body d-flex align-items-start justify-content-between">
      <div class="me-3">
        <p class="card-title mb-1 text-muted small">{{ $title }}</p>
        <h4 class="mb-0 fw-bold">{{ $value }}</h4>
        @if(!empty($subtitle))
        <small class="text-muted">{{ $subtitle }}</small>
        @endif
      </div>
      <div class="avatar flex-shrink-0">
        <span class="avatar-initial rounded bg-label-{{ $bgLabel }}">
          <i class="icon-base {{ $icon }} icon-24px"></i>
        </span>
      </div>
    </div>
  </div>
</div>
