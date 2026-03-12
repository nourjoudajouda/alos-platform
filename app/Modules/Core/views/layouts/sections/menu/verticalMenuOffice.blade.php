@php
$configData = Helper::appClasses();
$tenant = auth()->user()?->tenant;
$tenantLogoUrl = $tenant ? $tenant->getSettingsOrCreate()->logo_url : null;
$tenantDisplayName = $tenant ? $tenant->getSettingsOrCreate()->getDisplayName() : config('variables.templateName');
$companyMenuItems = [
  (object)['name' => 'Dashboard', 'url' => url('/company'), 'slug' => 'company.dashboard', 'icon' => 'menu-icon icon-base ti tabler-layout-dashboard'],
  (object)['name' => 'Clients', 'url' => url('/company/clients'), 'slug' => 'company.clients.index', 'icon' => 'menu-icon icon-base ti tabler-users-group'],
  (object)['name' => 'Cases', 'url' => url('/company/cases'), 'slug' => 'company.cases.index', 'icon' => 'menu-icon icon-base ti tabler-briefcase'],
  (object)['name' => 'Consultations', 'url' => url('/company/consultations'), 'slug' => 'company.consultations.index', 'icon' => 'menu-icon icon-base ti tabler-calendar-event'],
  (object)['name' => 'Subscription', 'url' => route('company.settings.subscription.show'), 'slug' => 'company.settings.subscription.show', 'icon' => 'menu-icon icon-base ti tabler-credit-card'],
  (object)['name' => 'Branding Settings', 'url' => route('company.settings.branding.edit'), 'slug' => 'company.settings.branding.edit', 'icon' => 'menu-icon icon-base ti tabler-palette'],
];
@endphp
{{-- قائمة لوحة التيننت: Dashboard فقط --}}
<aside id="layout-menu" class="layout-menu menu-vertical menu" @foreach(($configData['menuAttributes'] ?? []) as $attribute => $value) {{ $attribute }}="{{ $value }}" @endforeach>

  <div class="app-brand demo">
    <a href="{{ route('company.dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        @if ($tenantLogoUrl)
          <span class="app-brand-logo-wrapper d-inline-block" style="height: 60px;">
            <img src="{{ $tenantLogoUrl }}" alt="{{ $tenantDisplayName }}" class="app-brand-logo-img" style="height: 60px; width: auto; max-width: 140px; object-fit: contain;" />
          </span>
        @else
          @include('core::_partials.macros')
        @endif
      </span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="icon-base menu-toggle-icon d-none d-xl-block"></i>
      <i class="icon-base ti tabler-x d-block d-xl-none"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @foreach ($companyMenuItems as $menu)
    @php
      $current = Route::currentRouteName() ?? '';
      $slug = $menu->slug ?? '';
      $base = preg_replace('/\.index$/', '', $slug);
      $activeClass = ($current === $slug || ($base !== $slug && str_starts_with($current, $base . '.'))) ? 'active' : null;
    @endphp
    <li class="menu-item {{ $activeClass }}">
      <a href="{{ $menu->url ?? 'javascript:void(0);' }}" class="menu-link">
        @isset($menu->icon)<i class="{{ $menu->icon }}"></i>@endisset
        <div>{{ __($menu->name ?? '') }}</div>
      </a>
    </li>
    @endforeach
  </ul>

</aside>
