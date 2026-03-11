@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('portal::layouts.portal')

@section('title', __('Notifications') . ' — ' . config('app.name'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h4 class="fw-bold mb-0">{{ __('Notifications') }}</h4>
  @if($notifications->where('read_status', false)->isNotEmpty())
  <form action="{{ route('portal.notifications.read-all') }}" method="post" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Mark all as read') }}</button>
  </form>
  @endif
</div>

<div class="card">
  <div class="list-group list-group-flush">
    @forelse($notifications as $n)
    <div class="list-group-item {{ $n->read_status ? '' : 'bg-light' }}">
      <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
          <strong>{{ $n->title }}</strong>
          <p class="mb-1 small text-muted">{{ $n->message }}</p>
          <small class="text-muted">{{ $n->created_at?->format('Y-m-d H:i') }}</small>
        </div>
        <div>
          @if($n->link)
            <a href="{{ $n->link }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
          @endif
          @if(!$n->read_status)
            <form action="{{ route('portal.notifications.read', $n->id) }}" method="post" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Mark read') }}</button>
            </form>
          @endif
        </div>
      </div>
    </div>
    @empty
    <div class="list-group-item text-center text-muted py-5">
      {{ __('No notifications yet.') }}
    </div>
    @endforelse
  </div>
  @if($notifications->hasPages())
  <div class="card-footer">{{ $notifications->links() }}</div>
  @endif
</div>
@endsection
