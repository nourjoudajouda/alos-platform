@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Documents') . ' — ' . $client->name . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('admin.core.clients.show', $client) }}">{{ $client->name }}</a></li>
          <li class="breadcrumb-item active">{{ __('Documents') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ __('Document Center') }}</h4>
      <p class="text-muted small mb-0">{{ __('Upload and manage documents for :name. Internal documents are office-only; shared documents are visible to the client in the portal.', ['name' => $client->name]) }}</p>
    </div>
    <a href="{{ route('admin.core.clients.show', [$client, 'tab' => 'documents']) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to client') }}</a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  {{-- Upload --}}
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('Upload document') }}</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.core.clients.documents.store', $client) }}" method="post" enctype="multipart/form-data">
        @csrf
        @if(request('consultation_id'))<input type="hidden" name="consultation_id" value="{{ request('consultation_id') }}">@endif
        @if(request('case_id'))<input type="hidden" name="case_id" value="{{ request('case_id') }}">@endif
        <div class="row g-3">
          <div class="col-md-4">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="255" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label for="visibility" class="form-label">{{ __('Visibility') }}</label>
            <select name="visibility" id="visibility" class="form-select @error('visibility') is-invalid @enderror" required>
              <option value="{{ \App\Models\Document::VISIBILITY_INTERNAL }}" {{ old('visibility', \App\Models\Document::VISIBILITY_INTERNAL) === \App\Models\Document::VISIBILITY_INTERNAL ? 'selected' : '' }}>{{ __('Internal only') }}</option>
              <option value="{{ \App\Models\Document::VISIBILITY_SHARED }}" {{ old('visibility') === \App\Models\Document::VISIBILITY_SHARED ? 'selected' : '' }}>{{ __('Shared with client') }}</option>
            </select>
            @error('visibility')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label for="file" class="form-label">{{ __('File') }} <span class="text-muted small">(PDF, JPG, PNG, max 20 MB)</span></label>
            <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
            @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label for="description" class="form-label">{{ __('Description') }} <span class="text-muted small">({{ __('optional') }})</span></label>
            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ti tabler-upload {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Upload') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Filter --}}
  <div class="card mb-4">
    <div class="card-body py-2">
      <form action="{{ route('admin.core.clients.documents.index', $client) }}" method="get" class="d-flex flex-wrap align-items-center gap-2">
        <label for="filter_visibility" class="form-label mb-0">{{ __('Filter') }}:</label>
        <select name="visibility" id="filter_visibility" class="form-select form-select-sm" style="width: auto;">
          <option value="">{{ __('All') }}</option>
          <option value="{{ \App\Models\Document::VISIBILITY_INTERNAL }}" {{ ($filterVisibility ?? '') === \App\Models\Document::VISIBILITY_INTERNAL ? 'selected' : '' }}>{{ __('Internal only') }}</option>
          <option value="{{ \App\Models\Document::VISIBILITY_SHARED }}" {{ ($filterVisibility ?? '') === \App\Models\Document::VISIBILITY_SHARED ? 'selected' : '' }}>{{ __('Shared with client') }}</option>
        </select>
        @if(isset($consultations) && $consultations->isNotEmpty())
        <select name="consultation_id" id="filter_consultation" class="form-select form-select-sm" style="width: auto;">
          <option value="">{{ __('All consultations') }}</option>
          @foreach($consultations as $c)
            <option value="{{ $c->id }}" {{ ($filterConsultationId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->title }} ({{ $c->consultation_date?->format('Y-m-d') }})</option>
          @endforeach
        </select>
        @endif
        @if(isset($clientCases) && $clientCases->isNotEmpty())
        <select name="case_id" id="filter_case" class="form-select form-select-sm" style="width: auto;">
          <option value="">{{ __('All cases') }}</option>
          @foreach($clientCases as $c)
            <option value="{{ $c->id }}" {{ ($filterCaseId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->case_number }}</option>
          @endforeach
        </select>
        @endif
        <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Apply') }}</button>
      </form>
    </div>
  </div>

  {{-- List --}}
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('Documents') }}</h5>
    </div>
    <div class="card-body p-0">
      @forelse($documents as $doc)
        <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
          <div class="flex-shrink-0">
            <span class="avatar avatar-sm">
              <span class="avatar-initial rounded bg-label-secondary">
                <i class="icon-base ti tabler-file"></i>
              </span>
            </span>
          </div>
          <div class="flex-grow-1 min-w-0">
            <span class="fw-medium d-block">{{ $doc->name }}</span>
            <div class="small text-muted">
              {{ $doc->file_name }}
              @if ($doc->file_size)
                · {{ number_format($doc->file_size / 1024, 1) }} KB
              @endif
              · {{ $doc->created_at->format('Y-m-d H:i') }}
              @if ($doc->uploader)
                · {{ $doc->uploaded_by_type === \App\Models\Document::UPLOADED_BY_CLIENT ? __('Client') : $doc->uploader->name }}
              @endif
            </div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? 'bg-label-success' : 'bg-label-warning' }}">
              {{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? __('Shared') : __('Internal') }}
            </span>
            <a href="{{ route('admin.core.clients.documents.download', [$client, $doc]) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
              <i class="icon-base ti tabler-download"></i>
            </a>
            <form action="{{ route('admin.core.clients.documents.visibility', [$client, $doc]) }}" method="post" class="d-inline">
              @csrf
              @method('PUT')
              <input type="hidden" name="visibility" value="{{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? \App\Models\Document::VISIBILITY_INTERNAL : \App\Models\Document::VISIBILITY_SHARED }}">
              <button type="submit" class="btn btn-sm btn-outline-secondary" title="{{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? __('Make internal') : __('Share with client') }}">
                <i class="icon-base ti {{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? 'tabler-lock' : 'tabler-world' }}"></i>
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="text-center py-5 text-muted">
          <i class="icon-base ti tabler-folder-off icon-32px d-block mb-2"></i>
          <p class="mb-0">{{ __('No documents yet. Upload one above.') }}</p>
        </div>
      @endforelse
    </div>
    @if ($documents->hasPages())
      <div class="card-footer d-flex justify-content-center">
        {{ $documents->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
