@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseModel::STATUSES;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Case') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Add Case') }}</h4>
    <a href="{{ route(($caseRoutePrefix ?? 'admin.core.cases') . '.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-right {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
      {{ __('Back to list') }}
    </a>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route(($caseRoutePrefix ?? 'admin.core.cases') . '.store') }}" method="post">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label for="client_id" class="form-label">{{ __('Client') }} <span class="text-danger">*</span></label>
            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
              <option value="">{{ __('Select client') }}</option>
              @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_id', $preselectedClientId) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
              @endforeach
            </select>
            @error('client_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="case_number_display" class="form-label">{{ __('Case number') }} <span class="text-danger">*</span></label>
            <input type="text" id="case_number_display" class="form-control @error('case_number') is-invalid @enderror" value="{{ old('case_number', $defaultCaseNumber ?? '') }}" maxlength="255" placeholder="{{ $defaultCaseNumber ?? 'e.g. DE-025' }}" autocomplete="off" aria-required="true">
            <input type="hidden" name="case_number" id="case_number_hidden" value="{{ old('case_number', $defaultCaseNumber ?? '') }}">
            <div class="form-text small">{{ __('Based on tenant name. You can edit.') }}</div>
            @error('case_number')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="case_type" class="form-label">{{ __('Case type') }}</label>
            <input type="text" name="case_type" id="case_type" class="form-control @error('case_type') is-invalid @enderror" value="{{ old('case_type') }}" maxlength="255">
            @error('case_type')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="court_name" class="form-label">{{ __('Court name') }}</label>
            <input type="text" name="court_name" id="court_name" class="form-control @error('court_name') is-invalid @enderror" value="{{ old('court_name') }}" maxlength="255">
            @error('court_name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="responsible_lawyer_id" class="form-label">{{ __('Responsible lawyer') }}</label>
            <select name="responsible_lawyer_id" id="responsible_lawyer_id" class="form-select">
              <option value="">{{ __('None') }}</option>
              @foreach($assignableUsers as $u)
                <option value="{{ $u->id }}" {{ old('responsible_lawyer_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
              @endforeach
            </select>
            @error('responsible_lawyer_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
              @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" {{ old('status', 'open') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
              @endforeach
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <label for="description" class="form-label">{{ __('Description') }}</label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="5000">{{ old('description') }}</textarea>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Create Case') }}</button>
          <a href="{{ route(($caseRoutePrefix ?? 'admin.core.cases') . '.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
(function() {
  var form = document.querySelector('form[action*="core/cases"]');
  var display = document.getElementById('case_number_display');
  var hidden = document.getElementById('case_number_hidden');
  if (!form || !display || !hidden) return;
  function sync() { hidden.value = display.value; }
  display.addEventListener('input', sync);
  display.addEventListener('change', sync);
  form.addEventListener('submit', function() { sync(); });
  sync();
})();
</script>
@endsection
