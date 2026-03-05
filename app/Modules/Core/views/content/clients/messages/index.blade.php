@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Messages') . ' — ' . $client->name . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Messages') }}</h4>
      <p class="text-muted small mb-0">{{ __('Secure conversations with :name', ['name' => $client->name]) }}</p>
    </div>
    <div class="d-flex gap-2">
      <a href="{{ route('core.clients.show', [$client, 'tab' => 'messages']) }}" class="btn btn-outline-secondary btn-sm">
        <i class="icon-base ti tabler-arrow-left {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
        {{ __('Back to client') }}
      </a>
      <a href="{{ $showArchived ? route('core.clients.threads.index', $client) : route('core.clients.threads.index', [$client, 'archived' => 1]) }}" class="btn btn-outline-secondary btn-sm">
        {{ $showArchived ? __('Active conversations') : __('Archived') }}
      </a>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  {{-- New conversation --}}
  <div class="card mb-4">
    <div class="card-body">
      <form action="{{ route('core.clients.threads.store', $client) }}" method="post" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-8">
          <label for="subject" class="form-label small">{{ __('New conversation subject') }}</label>
          <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="{{ __('e.g. Contract review Q1') }}" value="{{ old('subject') }}" maxlength="255" required>
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

  {{-- Thread list --}}
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
            <a href="{{ route('core.clients.threads.show', [$client, $thread]) }}" class="fw-medium text-body d-block text-decoration-none">
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
          <div class="d-flex gap-2">
            <a href="{{ route('core.clients.threads.show', [$client, $thread]) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
            <form action="{{ route('core.clients.threads.archive', [$client, $thread]) }}" method="post" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-secondary">
                {{ $thread->archived_at ? __('Restore') : __('Archive') }}
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="text-center py-5 text-muted">
          <i class="icon-base ti tabler-message-off icon-32px d-block mb-2"></i>
          <p class="mb-0">{{ $showArchived ? __('No archived conversations.') : __('No conversations yet. Start one above.') }}</p>
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
