@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp

@extends('portal::layouts.portal')

@section('title', Str::limit($thread->subject, 50) . ' — ' . __('Client Portal'))

@section('content')
  <div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('portal.messages.index') }}">{{ __('Messages') }}</a></li>
            <li class="breadcrumb-item active">{{ Str::limit($thread->subject, 40) }}</li>
          </ol>
        </nav>
        <h4 class="fw-bold mb-0">{{ $thread->subject }}</h4>
        @if ($thread->archived_at)
          <span class="badge bg-label-warning mt-1">{{ __('Archived') }}</span>
        @endif
      </div>
      <a href="{{ route('portal.messages.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if (session('error'))
      <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @php
      $allAttachments = $thread->messages->flatMap(function ($m) { return $m->attachments->map(fn ($a) => (object)['attachment' => $a, 'message' => $m]); });
    @endphp
    @if ($allAttachments->isNotEmpty())
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="icon-base ti tabler-paperclip"></i>
          <span>{{ __('Attachments in this conversation') }}</span>
          <span class="badge bg-label-secondary ms-auto">{{ $allAttachments->count() }}</span>
        </div>
        <div class="card-body">
          <div class="row g-2">
            @foreach ($allAttachments as $item)
              @php $att = $item->attachment; @endphp
              <div class="col-12 col-sm-6 col-md-4">
                <a href="{{ route('portal.messages.attachments.download', [$thread, $att]) }}" class="d-flex align-items-center gap-2 p-2 rounded bg-light text-body text-decoration-none border border-secondary" target="_blank" rel="noopener">
                  <i class="icon-base ti tabler-file flex-shrink-0 text-muted"></i>
                  <div class="min-w-0 flex-grow-1">
                    <span class="d-block text-truncate small fw-medium" title="{{ $att->name }}">{{ $att->name }}</span>
                    @if ($att->size)
                      <span class="text-muted small">{{ number_format($att->size / 1024, 1) }} KB</span>
                    @endif
                  </div>
                  <i class="icon-base ti tabler-download flex-shrink-0 text-primary"></i>
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @endif

    <div class="card mb-4">
      <div class="card-body">
        <div class="d-flex flex-column gap-3">
          @forelse($thread->messages as $msg)
            <div class="d-flex gap-3 {{ $msg->isFromClient() ? ($contentDir === 'rtl' ? 'flex-row-reverse' : '') : '' }}">
              <div class="flex-shrink-0">
                <span class="avatar avatar-sm">
                  <span class="avatar-initial rounded {{ $msg->isFromClient() ? 'bg-label-info' : 'bg-label-primary' }}">
                    {{ strtoupper(mb_substr($msg->user->name ?? '?', 0, 1)) }}
                  </span>
                </span>
              </div>
              <div class="flex-grow-1 min-w-0 {{ $msg->isFromClient() ? 'text-end' : '' }}">
                <div class="d-flex align-items-center gap-2 small mb-1 {{ $msg->isFromClient() ? 'justify-content-end' : '' }}">
                  <span class="fw-medium">{{ $msg->user->name ?? __('Unknown') }}</span>
                  @if ($msg->isFromClient())
                    <span class="badge bg-label-info">{{ __('You') }}</span>
                  @else
                    <span class="badge bg-label-primary">{{ __('Office') }}</span>
                  @endif
                  <span class="text-muted">{{ $msg->created_at->format('Y-m-d H:i') }}</span>
                </div>
                <div class="bg-light rounded p-2 text-break">{{ nl2br(e($msg->body)) }}</div>
                @if ($msg->attachments->isNotEmpty())
                  <div class="mt-2 small">
                    <span class="text-muted {{ $contentDir === 'rtl' ? 'ms-2' : 'me-2' }}">{{ __('Attachments') }}:</span>
                    @foreach ($msg->attachments as $att)
                      <a href="{{ route('portal.messages.attachments.download', [$thread, $att]) }}" class="d-inline-flex align-items-center gap-1 rounded px-2 py-1 bg-white border border-secondary text-body text-decoration-none {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }} mb-1" target="_blank" rel="noopener" title="{{ $att->name }}">
                        <i class="icon-base ti tabler-file-download"></i>
                        <span class="text-truncate" style="max-width: 180px;">{{ $att->name }}</span>
                      </a>
                    @endforeach
                  </div>
                @endif
              </div>
            </div>
          @empty
            <p class="text-muted text-center py-4 mb-0">{{ __('No messages in this conversation yet.') }}</p>
          @endforelse
        </div>
      </div>
    </div>

    @if (!$thread->archived_at && $canSend)
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Reply') }}</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('portal.messages.reply', $thread) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="body" class="form-label">{{ __('Message') }}</label>
              <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="4" maxlength="10000" required>{{ old('body') }}</textarea>
              @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @if ($canUpload)
              <div class="mb-3">
                <label for="attachments" class="form-label">{{ __('Attachments') }} <span class="text-muted small">({{ __('optional, max 10 MB each') }})</span></label>
                <input type="file" name="attachments[]" id="attachments" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif">
                @error('attachments.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            @endif
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ti tabler-send {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Send') }}
            </button>
          </form>
        </div>
      </div>
    @endif
  </div>
@endsection
