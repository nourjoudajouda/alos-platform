@php
  $configData = Helper::appClasses();
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Role') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add Role') }}</h4>
    <a href="{{ route('admin.core.roles.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.core.roles.store') }}" method="post">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
          <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. editor">
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('Permissions') }}</label>
          <div class="row g-2">
            @foreach($permissions as $group => $items)
              <div class="col-12 col-md-6 col-lg-4">
                <div class="border rounded p-2">
                  <small class="text-muted d-block mb-2 text-uppercase">{{ $group }}</small>
                  @foreach($items as $p)
                    <div class="form-check">
                      <input type="checkbox" name="permissions[]" value="{{ $p->name }}" id="perm_{{ $p->id }}" class="form-check-input" {{ in_array($p->name, old('permissions', [])) ? 'checked' : '' }}>
                      <label for="perm_{{ $p->id }}" class="form-check-label small">{{ $p->name }}</label>
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Create Role') }}</button>
          <a href="{{ route('admin.core.roles.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
