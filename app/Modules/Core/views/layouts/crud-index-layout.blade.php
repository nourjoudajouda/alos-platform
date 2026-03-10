{{--
  ALOS — هيكل قياسي لجميع صفحات قوائم CRUD (Index).
  استخدمه في كل CRUD: حدّد المتغيرات ثم املأ الـ sections.

  متغيرات مطلوبة (من الـ view أو الـ controller):
  - $crudIndexId: معرف فريد (مثلاً tenants, roles, users)
  - $crudIndexTitle: عنوان الصفحة
  - $crudIndexFiltersAction: رابط action لفورم الفلاتر
  - $crudIndexPerPage: قيمة per_page الحالية
  - $crudIndexTableTitle: عنوان بطاقة الجدول
  - $crudIndexAddUrl: رابط زر الإضافة
  - $crudIndexAddLabel: نص زر الإضافة
  - $crudIndexEmptyMessage: رسالة عدم وجود بيانات
  - $crudIndexEmptyLink: رابط في رسالة الفراغ
  - $crudIndexEmptyLinkText: نص الرابط
  - $crudIndexEmptyColspan: عدد أعمدة الجدول
  - $crudIndexEmptyIcon: أيقونة (كلاس مثل ti tabler-building-store)
  - $items: الـ paginator أو Collection للـ hasPages()

  sections يجب تعريفها:
  - crud_stats: صف الكروت الإحصائية (أو فارغ)
  - crud_filters_hidden_inputs: حقول hidden إضافية داخل فورم الفلاتر (أو فارغ)
  - crud_offcanvas: محتوى الـ offcanvas (فورم فلتر جانبي) + زر الفلتر يفتحه (أو فارغ)
  - crud_table_header: <th>...</th> لأعمدة الجدول
  - crud_table_body: @forelse صفوف الجدول
  - crud_pagination: روابط الـ pagination (أو فارغ)
  - crud_offcanvas_script: سكربت إضافي لـ change على عناصر الـ offcanvas (أو فارغ)
--}}
@php
  $configData = Helper::appClasses();
  $isRtl = ($configData['textDirection'] ?? 'ltr') === 'rtl';
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $cid = $crudIndexId ?? 'crud';
  $showViewToggle = $crudIndexShowViewToggle ?? true;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $crudIndexTitle ?? '')

@section('page-style')
@include('core::_partials.crud-table-styles')
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  @hasSection('crud_description')
  <div class="alert alert-secondary border-0 mb-4 py-2 px-3" role="note">
    <small class="text-muted">@yield('crud_description')</small>
  </div>
  @endif
  @hasSection('crud_stats')
  <div class="row g-4 mb-4">
    @yield('crud_stats')
  </div>
  @endif

  <div class="card mb-4">
    <div class="card-header py-3">
      <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
    </div>
    <div class="card-body">
      <form action="{{ $crudIndexFiltersAction }}" method="get" id="crudFiltersForm-{{ $cid }}">
        @yield('crud_filters_hidden_inputs')
        <div class="d-flex flex-column flex-md-row flex-wrap align-items-stretch align-items-md-end gap-3">
          <div class="d-flex flex-wrap align-items-end gap-3">
            <div class="filter-field">
              <label for="crudPerPage-{{ $cid }}" class="form-label small text-muted mb-1">{{ __('Per Page') }}</label>
              <select name="per_page" id="crudPerPage-{{ $cid }}" class="form-select form-select-sm" style="width: 5rem;">
                @foreach([10, 25, 50, 100] as $n)
                  <option value="{{ $n }}" {{ (int) $crudIndexPerPage === $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
              </select>
            </div>
            @hasSection('crud_offcanvas')
            <div class="filter-field">
              <label class="form-label small text-muted mb-1 d-block">{{ __('Filters') }}</label>
              <button type="button" class="btn btn-outline-primary btn-sm" id="crudFiltersBtn-{{ $cid }}" aria-controls="crudOffcanvas-{{ $cid }}">
                <i class="icon-base ti tabler-filter {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
                {{ __('Filters') }}
              </button>
            </div>
            @endif
          </div>
          <div class="vr d-none d-md-block opacity-25 flex-shrink-0" style="align-self: stretch;"></div>
          <div class="d-flex flex-wrap align-items-end gap-2 {{ $isRtl ? 'me-auto' : 'ms-md-auto' }}">
            <div class="input-group input-group-merge" style="width: 12rem;">
              <input type="search" name="search" class="form-control form-control-sm" placeholder="{{ __('Search placeholder') }}" value="{{ request('search') }}" aria-label="{{ __('Search') }}">
              <button type="submit" class="btn btn-primary btn-sm" aria-label="{{ __('Search') }}">
                <i class="icon-base ti tabler-search"></i>
              </button>
            </div>
            @if($showViewToggle)
            <div class="btn-group btn-group-sm" role="group">
              @php $currentView = request('view', 'list'); @endphp
              <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="btn {{ $currentView === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }} btn-icon" title="{{ __('Grid view') }}">
                <i class="icon-base ti tabler-layout-grid"></i>
              </a>
              <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="btn {{ $currentView === 'list' ? 'btn-primary' : 'btn-outline-secondary' }} btn-icon" title="{{ __('List view') }}">
                <i class="icon-base ti tabler-list"></i>
              </a>
            </div>
            @endif
          </div>
        </div>
      </form>
    </div>
  </div>

  @hasSection('crud_offcanvas')
  <div class="offcanvas offcanvas-{{ $isRtl ? 'start' : 'end' }}" tabindex="-1" id="crudOffcanvas-{{ $cid }}" aria-labelledby="crudOffcanvasLabel-{{ $cid }}">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="crudOffcanvasLabel-{{ $cid }}">{{ __('Filters') }}</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body">
      @yield('crud_offcanvas')
    </div>
  </div>
  @endif

  <div class="card crud-table {{ $cid }}-table">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
      <h5 class="card-title mb-0">{{ $crudIndexTableTitle }}</h5>
      @if(($crudIndexShowAddButton ?? true) && !empty($crudIndexAddUrl))
      <a href="{{ $crudIndexAddUrl }}" class="btn btn-primary">
        <i class="icon-base ti tabler-plus icon-20px {{ $isRtl ? 'ms-1' : 'me-1' }}"></i>
        {{ $crudIndexAddLabel }}
      </a>
      @endif
    </div>
    <div class="table-responsive">
      <table class="table table-hover" dir="{{ $contentDir }}">
        <thead>
          <tr>
            @yield('crud_table_header')
          </tr>
        </thead>
        <tbody>
          @yield('crud_table_body')
        </tbody>
      </table>
    </div>
    @if(isset($items) && $items->hasPages())
    <div class="card-footer">
      {{ $items->links() }}
    </div>
    @endif
  </div>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible mt-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible mt-3" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
</div>

<script>
(function() {
  var cid = '{{ $cid }}';
  function init() {
    var form = document.getElementById('crudFiltersForm-' + cid);
    var perPage = document.getElementById('crudPerPage-' + cid);
    if (perPage && form) perPage.addEventListener('change', function() { form.submit(); });
    @hasSection('crud_offcanvas')
    var btn = document.getElementById('crudFiltersBtn-' + cid);
    var oc = document.getElementById('crudOffcanvas-' + cid);
    if (btn && oc && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
      btn.addEventListener('click', function() { bootstrap.Offcanvas.getOrCreateInstance(oc).show(); });
    }
    @endif
  }
  if (document.readyState === 'complete') init(); else window.addEventListener('load', init);
})();
</script>
@yield('crud_extra_script')
@endsection
