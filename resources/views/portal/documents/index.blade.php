@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp

@extends('portal::layouts.portal')

@section('title', __('Documents') . ' — ' . __('Client Portal'))

@section('content')
  <div class="mb-4">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">{{ __('Dashboard') }}</a></li>
        <li class="breadcrumb-item active">{{ __('Documents') }}</li>
      </ol>
    </nav>
    <h4 class="fw-bold mb-1">{{ __('Documents') }}</h4>
    <p class="text-muted small mb-0">{{ __('Shared documents from the office. You can also upload documents for the office to review.') }}</p>
  </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    {{-- Upload (client uploads = internal until office shares) --}}
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Upload document for the office') }}</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('portal.documents.store') }}" method="post" enctype="multipart/form-data">
          @csrf
          <div class="row g-3">
            <div class="col-md-4">
              <label for="name" class="form-label">{{ __('Name') }}</label>
              <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="255" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label for="file" class="form-label">{{ __('File') }} <span class="text-muted small">(PDF, JPG, PNG, max 20 MB)</span></label>
              <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" required>
              @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button type="submit" class="btn btn-primary">
                <i class="icon-base ti tabler-upload {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
                {{ __('Upload') }}
              </button>
            </div>
            <div class="col-12">
              <label for="description" class="form-label">{{ __('Description') }} <span class="text-muted small">({{ __('optional') }})</span></label>
              <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000">{{ old('description') }}</textarea>
              @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="form-text small">{{ __('Uploaded documents are visible to the office. They may share them with you later.') }}</div>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Shared documents list --}}
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Shared with you') }}</h5>
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
                @php
                  $ext = strtolower(pathinfo($doc->file_name ?? '', PATHINFO_EXTENSION));
                  $typeLabel = match($ext) { 'pdf' => 'PDF', 'jpg' => 'JPG', 'jpeg' => 'JPEG', 'png' => 'PNG', default => strtoupper($ext) ?: __('File') };
                @endphp
                <span class="badge bg-label-secondary me-1">{{ $typeLabel }}</span>
                {{ $doc->file_name }}
                @if ($doc->file_size)
                  · {{ number_format($doc->file_size / 1024, 1) }} KB
                @endif
                · {{ $doc->created_at->format('Y-m-d H:i') }}
              </div>
            </div>
            <a href="{{ route('portal.documents.download', $doc) }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
              <i class="icon-base ti tabler-download {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Download') }}
            </a>
          </div>
        @empty
          <div class="text-center py-5 px-4">
            <i class="icon-base ti tabler-folder-off icon-32px text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">{{ __('No documents shared with you yet.') }}</p>
            <p class="small text-muted mt-1">{{ __('When the office shares documents with you, they will appear here.') }}</p>
          </div>
        @endforelse
      </div>
      @if ($documents->hasPages())
        <div class="card-footer d-flex justify-content-center">
          {{ $documents->links() }}
        </div>
      @endif
    </div>
@endsection
