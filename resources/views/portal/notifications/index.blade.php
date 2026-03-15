@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('portal::layouts.portal')

@section('title', __('Notifications') . ' — ' . config('app.name'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <h4 class="fw-bold mb-0">{{ __('Notifications') }}</h4>
  <div class="d-flex flex-wrap align-items-center gap-2">
    @if(request()->filled('unread'))
      <a href="{{ route('portal.notifications.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('All notifications') }}</a>
    @else
      <a href="{{ route('portal.notifications.index', ['unread' => 1]) }}" class="btn btn-sm btn-outline-secondary">{{ __('Unread only') }}</a>
    @endif
    @if($notifications->where('read_status', false)->isNotEmpty())
    <form action="{{ route('portal.notifications.read-all') }}" method="post" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Mark all as read') }}</button>
    </form>
    @endif
  </div>
</div>

<div class="card">
  <div class="list-group list-group-flush">
    @forelse($notifications as $n)
    <div class="list-group-item {{ $n->read_status ? '' : 'bg-light border-start border-primary border-3' }}">
      <div class="d-flex justify-content-between align-items-start gap-2">
        <div class="flex-grow-1 min-width-0">
          <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <strong class="{{ $n->read_status ? '' : 'text-primary' }}">{{ $n->title }}</strong>
            <span class="badge bg-label-secondary" style="font-size: 0.7rem;">{{ $n->type_label }}</span>
          </div>
          <p class="mb-1 small text-body-secondary">{{ $n->message }}</p>
          <small class="text-muted">{{ $n->created_at?->format('Y-m-d H:i') }} · {{ $n->created_at?->diffForHumans() }}</small>
        </div>
        <div class="flex-shrink-0 d-flex flex-wrap gap-1 justify-content-end">
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
    <div class="list-group-item text-center py-5">
      <i class="icon-base ti tabler-bell-off text-muted mb-2" style="font-size: 2.5rem;"></i>
      <p class="mb-0 text-muted">{{ __('No notifications yet.') }}</p>
      <p class="small text-muted mt-1">{{ __('When you have new messages, shared documents, session reminders, or reports, they will appear here.') }}</p>
    </div>
    @endforelse
  </div>
  @if($notifications->hasPages())
  <div class="card-footer">{{ $notifications->links() }}</div>
  @endif
</div>
@endsection
