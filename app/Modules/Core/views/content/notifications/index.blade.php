@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Notifications') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  @if(isset($isOfficeUser) && !$isOfficeUser)
  <div class="alert alert-info mb-4" role="alert">
    {{ __('You are logged in as administrator. In-app notifications are sent to office users (tenant staff) and clients.') }}
  </div>
  @endif
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Notifications') }}</h4>
      <p class="text-muted small mb-0">{{ __('Your in-app notifications.') }}</p>
    </div>
    @if($notifications->where('read_status', false)->isNotEmpty())
    <form action="{{ route('admin.core.notifications.read-all') }}" method="post" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Mark all as read') }}</button>
    </form>
    @endif
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Title') }}</th>
            <th>{{ __('Time') }}</th>
            <th>{{ __('Status') }}</th>
            <th width="120">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($notifications as $n)
          <tr class="{{ $n->read_status ? '' : 'table-active' }}">
            <td><span class="badge bg-label-secondary">{{ $n->type }}</span></td>
            <td>
              <strong>{{ $n->title }}</strong>
              @if($n->message)<br><small class="text-muted">{{ Str::limit($n->message, 80) }}</small>@endif
            </td>
            <td class="text-nowrap small">{{ $n->created_at?->format('Y-m-d H:i') }}</td>
            <td>
              @if($n->read_status)
                <span class="badge bg-label-success">{{ __('Read') }}</span>
              @else
                <span class="badge bg-label-warning">{{ __('Unread') }}</span>
              @endif
            </td>
            <td>
              @if($n->link)
                <a href="{{ $n->link }}" class="btn btn-sm btn-text-primary">{{ __('View') }}</a>
              @endif
              @if(!$n->read_status)
                <form action="{{ route('admin.core.notifications.read', $n->id) }}" method="post" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-text-secondary">{{ __('Mark read') }}</button>
                </form>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-5">
              <i class="icon-base ti tabler-bell-off icon-32px d-block mb-2 opacity-50"></i>
              @if(isset($isOfficeUser) && !$isOfficeUser)
                {{ __('No notifications for this account.') }}
              @else
                {{ __('No notifications yet.') }}
              @endif
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($notifications->hasPages())
    <div class="card-footer">
      {{ $notifications->links() }}
    </div>
    @endif
  </div>
</div>
@endsection
