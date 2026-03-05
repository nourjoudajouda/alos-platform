@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp

@extends('portal::layouts.portal')

@section('title', __('Messages') . ' — ' . __('Client Portal'))

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="fw-bold mb-1">{{ __('Messages') }}</h4>
        <p class="text-muted small mb-0">{{ __('Secure conversations with the office.') }}</p>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ $showArchived ? route('portal.messages.index') : route('portal.messages.index', ['archived' => 1]) }}" class="btn btn-outline-secondary btn-sm">
          {{ $showArchived ? __('Active conversations') : __('Archived') }}
        </a>
      </div>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    @if ($canSend)
      <div class="card mb-4">
        <div class="card-body">
          <form action="{{ route('portal.messages.store') }}" method="post" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-8">
              <label for="subject" class="form-label small">{{ __('New conversation subject') }}</label>
              <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="{{ __('e.g. Question about my case') }}" value="{{ old('subject') }}" maxlength="255" required>
              @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-primary w-100">
                <i class="icon-base ti tabler-plus {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
                {{ __('Start conversation') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ $showArchived ? __('Archived conversations') : __('Conversations') }}</h5>
      </div>
      <div class="card-body p-0">
        @forelse($threads as $thread)
          @php
            $lastMsg = $thread->messages->first();
            $lastSender = $lastMsg?->user;
          @endphp
          <div class="border-bottom border-secondary p-3 d-flex align-items-center gap-3 flex-wrap">
            <div class="flex-grow-1 min-w-0">
              <a href="{{ route('portal.messages.show', $thread) }}" class="fw-medium text-body d-block text-decoration-none">
                {{ $thread->subject }}
              </a>
              @if ($lastMsg)
                <p class="text-muted small mb-0 mt-1">
                  {{ $lastSender ? $lastSender->name : __('System') }} · {{ $lastMsg->created_at->diffForHumans() }}
                </p>
              @else
                <p class="text-muted small mb-0 mt-1">{{ __('No messages yet') }}</p>
              @endif
            </div>
            <a href="{{ route('portal.messages.show', $thread) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
          </div>
        @empty
          <div class="text-center py-5 text-muted">
            <i class="icon-base ti tabler-message-off icon-32px d-block mb-2"></i>
            <p class="mb-0">{{ $showArchived ? __('No archived conversations.') : ($canSend ? __('No conversations yet. Start one above.') : __('No conversations yet.')) }}</p>
          </div>
        @endforelse
      </div>
      @if ($threads->hasPages())
        <div class="card-footer d-flex justify-content-center">
          {{ $threads->links() }}
        </div>
      @endif
    </div>
  </div>
@endsection
