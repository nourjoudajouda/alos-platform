@isset($pageConfigs)
  {!! Helper::updatePageConfig($pageConfigs) !!}
@endisset
@php
  $configData = Helper::appClasses();
@endphp

@isset($configData['layout'])
  @include(
      $configData['layout'] === 'horizontal'
          ? 'core::layouts.horizontalLayout'
          : ($configData['layout'] === 'blank'
              ? 'core::layouts.blankLayout'
              : ($configData['layout'] === 'front'
                  ? 'core::layouts.layoutFront'
                  : 'core::layouts.contentNavbarLayout')))
@endisset
