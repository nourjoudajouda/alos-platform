@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\CaseSession::STATUSES;
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Add Session') . ' — ' . $case->case_number . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="{{ route('admin.core.cases.show', $case) }}">{{ $case->case_number }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.core.cases.sessions.index', $case) }}">{{ __('Sessions') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Add Session') }}</li>
      </ol>
    </nav>
    <a href="{{ route('admin.core.cases.sessions.index', $case) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to sessions') }}</a>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('Add Court Hearing') }}</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.core.cases.sessions.store', $case) }}" method="post">
        @csrf
        <div class="row g-3">
          <div class="col-md-6">
            <label for="session_date" class="form-label">{{ __('Date') }} <span class="text-danger">*</span></label>
            <input type="date" name="session_date" id="session_date" class="form-control @error('session_date') is-invalid @enderror" value="{{ old('session_date') }}" required>
            @error('session_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="session_time" class="form-label">{{ __('Time') }}</label>
            <input type="time" name="session_time" id="session_time" class="form-control @error('session_time') is-invalid @enderror" value="{{ old('session_time') }}">
            @error('session_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="court_name" class="form-label">{{ __('Court name') }}</label>
            <input type="text" name="court_name" id="court_name" class="form-control @error('court_name') is-invalid @enderror" value="{{ old('court_name', $case->court_name) }}" maxlength="255">
            @error('court_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="location" class="form-label">{{ __('Location') }}</label>
            <input type="text" name="location" id="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" maxlength="255">
            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label for="assigned_to" class="form-label">{{ __('Assigned to') }}</label>
            <select name="assigned_to" id="assigned_to" class="form-select">
              <option value="">{{ __('None') }}</option>
              @foreach($assignableUsers as $u)
                <option value="{{ $u->id }}" {{ old('assigned_to') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
              @foreach($statusLabels as $val => $label)
                <option value="{{ $val }}" {{ old('status', 'scheduled') === $val ? 'selected' : '' }}>{{ __($label) }}</option>
              @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label for="notes" class="form-label">{{ __('Notes') }}</label>
            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">{{ __('Create Session') }}</button>
            <a href="{{ route('admin.core.cases.sessions.index', $case) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
