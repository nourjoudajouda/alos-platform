@extends('core::layouts.layoutMaster')

@section('title', __('Office Dashboard') . ' — ' . (auth()->user()->tenant?->name ?? config('app.name')))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Office Dashboard') }}</h4>
      <p class="text-body mb-0 small">{{ __('Your office panel. External site and more features will be linked to your domain.') }}</p>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body text-center py-6">
          <span class="avatar avatar-lg rounded-circle bg-label-primary mb-3">
            <i class="icon-base ti tabler-building-store icon-32px"></i>
          </span>
          <h5 class="mb-2">{{ auth()->user()->tenant?->name ?? __('Your Office') }}</h5>
          <p class="text-muted mb-0">{{ __('This panel is dedicated to your office. Content and links will be added here.') }}</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
