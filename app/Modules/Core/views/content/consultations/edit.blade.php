@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\Consultation::STATUSES;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Edit Consultation') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h4 class="fw-bold mb-0">{{ __('Edit Consultation') }}: {{ $consultation->title }}</h4>
    <div class="d-flex gap-2">
      <a href="{{ route('admin.core.consultations.show', $consultation) }}" class="btn btn-outline-secondary btn-sm">{{ __('View') }}</a>
      <a href="{{ route('admin.core.consultations.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form action="{{ route('admin.core.consultations.update', $consultation) }}" method="post">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-md-6">
            <label for="client_id" class="form-label">{{ __('Client') }} <span class="text-danger">*</span></label>
            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
              @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ old('client_id', $consultation->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
              @endforeach
            </select>
            @error('client_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="consultation_date" class="form-label">{{ __('Consultation date') }} <span class="text-danger">*</span></label>
            <input type="date" name="consultation_date" id="consultation_date" class="form-control @error('consultation_date') is-invalid @enderror" value="{{ old('consultation_date', $consultation->consultation_date?->format('Y-m-d')) }}" required>
            @error('consultation_date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="responsible_user_id" class="form-label">{{ __('Responsible user') }}</label>
            <select name="responsible_user_id" id="responsible_user_id" class="form-select">
              <option value="">{{ __('None') }}</option>
              @foreach($assignableUsers as $u)
                <option value="{{ $u->id }}" {{ old('responsible_user_id', $consultation->responsible_user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
              @endforeach
            </select>
            @error('responsible_user_id')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-6">
            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
              @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" {{ old('status', $consultation->status) === $val ? 'selected' : '' }}>{{ __($label) }}</option>
              @endforeach
            </select>
            @error('status')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $consultation->title) }}" maxlength="255" required>
            @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <label for="summary" class="form-label">{{ __('Summary') }}</label>
            <textarea name="summary" id="summary" class="form-control @error('summary') is-invalid @enderror" rows="3" maxlength="5000">{{ old('summary', $consultation->summary) }}</textarea>
            <div class="form-text small">{{ __('Summary visible to client when shared.') }}</div>
            @error('summary')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <label for="internal_notes" class="form-label">{{ __('Internal notes') }}</label>
            <textarea name="internal_notes" id="internal_notes" class="form-control @error('internal_notes') is-invalid @enderror" rows="2" maxlength="5000">{{ old('internal_notes', $consultation->internal_notes) }}</textarea>
            <div class="form-text small">{{ __('Internal notes are never shown to the client.') }}</div>
            @error('internal_notes')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-12">
            <div class="form-check">
              <input type="checkbox" name="is_shared_with_client" id="is_shared_with_client" value="1" class="form-check-input" {{ old('is_shared_with_client', $consultation->is_shared_with_client) ? 'checked' : '' }}>
              <label for="is_shared_with_client" class="form-check-label">{{ __('Share with client in portal') }}</label>
            </div>
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary">{{ __('Update Consultation') }}</button>
          <a href="{{ route('admin.core.consultations.show', $consultation) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
