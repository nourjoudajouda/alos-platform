@php
  $configData = Helper::appClasses();
  $consultationRoutePrefix = $consultationRoutePrefix ?? 'admin.core.consultations';
  $clientRoutePrefix = $clientRoutePrefix ?? 'admin.core.clients';
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $statusLabels = \App\Models\Consultation::STATUSES;
  $statusClass = match($consultation->status) {
    'open' => 'primary',
    'completed' => 'success',
    'archived' => 'secondary',
    default => 'secondary',
  };
  $linkedThreads = $consultation->messageThreads ?? collect();
  $unlinkedThreads = $availableThreads->filter(fn ($t) => !$t->consultation_id);
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $consultation->title . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  {{-- Consultation header --}}
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-xl">
            <span class="avatar-initial rounded bg-label-primary"><i class="icon-base ti tabler-calendar-event"></i></span>
          </span>
          <div>
            <h4 class="mb-1">{{ $consultation->title }}</h4>
            <p class="text-muted mb-1 small">{{ $consultation->consultation_date?->format('Y-m-d') ?? '—' }}</p>
            <a href="{{ route($clientRoutePrefix . '.show', $consultation->client) }}" class="text-primary small">{{ $consultation->client->name }}</a>
            <span class="badge bg-label-{{ $statusClass }} ms-2">{{ __($statusLabels[$consultation->status] ?? $consultation->status) }}</span>
            @if($consultation->is_shared_with_client)
              <span class="badge bg-label-info ms-1">{{ __('Shared with client') }}</span>
            @endif
            <span class="text-muted small ms-2">{{ __('Updated') }} {{ $consultation->updated_at?->format('Y-m-d') }}</span>
          </div>
        </div>
        <div class="d-flex gap-2">
          @can('consultations.manage')
          <a href="{{ route($consultationRoutePrefix . '.edit', $consultation) }}" class="btn btn-warning btn-sm">
            <i class="icon-base ti tabler-pencil {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Edit') }}
          </a>
          <form action="{{ route($consultationRoutePrefix . '.destroy', $consultation) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this consultation?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Delete') }}</button>
          </form>
          @endcan
          <a href="{{ route($clientRoutePrefix . '.show', [$consultation->client, 'tab' => 'consultations']) }}" class="btn btn-outline-secondary btn-sm">{{ __('Client profile') }}</a>
          <a href="{{ route($consultationRoutePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
        </div>
      </div>
    </div>
  </div>

  @if (session('success'))
  <div class="alert alert-success alert-dismissible mb-4" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  <div class="row">
    {{-- Overview --}}
    <div class="col-lg-8">
      <div class="card mb-4">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Overview') }}</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Title') }}</dt>
            <dd class="col-sm-9">{{ $consultation->title }}</dd>
            <dt class="col-sm-3">{{ __('Client') }}</dt>
            <dd class="col-sm-9"><a href="{{ route($clientRoutePrefix . '.show', $consultation->client) }}">{{ $consultation->client->name }}</a></dd>
            <dt class="col-sm-3">{{ __('Consultation date') }}</dt>
            <dd class="col-sm-9">{{ $consultation->consultation_date?->format('Y-m-d') ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Responsible') }}</dt>
            <dd class="col-sm-9">{{ $consultation->responsibleUser?->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Status') }}</dt>
            <dd class="col-sm-9"><span class="badge bg-label-{{ $statusClass }}">{{ __($statusLabels[$consultation->status] ?? $consultation->status) }}</span></dd>
            <dt class="col-sm-3">{{ __('Summary') }}</dt>
            <dd class="col-sm-9">{{ $consultation->summary ? nl2br(e($consultation->summary)) : '—' }}</dd>
            <dt class="col-sm-3">{{ __('Internal notes') }}</dt>
            <dd class="col-sm-9"><span class="text-muted">{{ $consultation->internal_notes ? nl2br(e($consultation->internal_notes)) : '—' }}</span></dd>
          </dl>
        </div>
      </div>
    </div>

    {{-- Documents --}}
    <div class="col-lg-12">
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Documents') }}</h5>
          <a href="{{ route($clientRoutePrefix . '.documents.index', ['client' => $consultation->client, 'consultation_id' => $consultation->id]) }}" class="btn btn-primary btn-sm">
            <i class="icon-base ti tabler-folder-plus {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Open Document Center') }}
          </a>
        </div>
        <div class="card-body">
          @php $consultationDocs = $consultation->documents; @endphp
          @if($consultationDocs->isEmpty())
            <div class="text-center text-muted py-4">
              <i class="icon-base ti tabler-folder-off icon-32px d-block mb-2 opacity-50"></i>
              <p class="mb-0">{{ __('No documents linked to this consultation.') }}</p>
              <a href="{{ route($clientRoutePrefix . '.documents.index', ['client' => $consultation->client, 'consultation_id' => $consultation->id]) }}" class="btn btn-outline-primary btn-sm mt-2">{{ __('Add document') }}</a>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Visibility') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($consultationDocs as $doc)
                    <tr>
                      <td>{{ $doc->name }}</td>
                      <td>
                        <span class="badge {{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? 'bg-label-success' : 'bg-label-warning' }}">
                          {{ $doc->visibility === \App\Models\Document::VISIBILITY_SHARED ? __('Shared') : __('Internal') }}
                        </span>
                      </td>
                      <td class="text-muted small">{{ $doc->created_at?->format('Y-m-d H:i') }}</td>
                      <td>
                        <a href="{{ route($clientRoutePrefix . '.documents.download', [$consultation->client, $doc]) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('Download') }}">
                          <i class="icon-base ti tabler-download"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Message threads --}}
    <div class="col-lg-12">
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Linked message threads') }}</h5>
          @can('consultations.manage')
          <div class="d-flex gap-2">
            @if($unlinkedThreads->isNotEmpty())
            <div class="dropdown">
              <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="icon-base ti tabler-link {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
                {{ __('Link existing thread') }}
              </button>
              <ul class="dropdown-menu">
                @foreach($unlinkedThreads as $t)
                  <li>
                    <form action="{{ route($consultationRoutePrefix . '.link-thread', $consultation) }}" method="post" class="d-inline">
                      @csrf
                      <input type="hidden" name="thread_id" value="{{ $t->id }}">
                      <button type="submit" class="dropdown-item">{{ $t->subject }} ({{ $t->created_at?->format('Y-m-d') }})</button>
                    </form>
                  </li>
                @endforeach
              </ul>
            </div>
            @endif
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createThreadModal">
              <i class="icon-base ti tabler-plus {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Create new thread') }}
            </button>
          </div>
          @endcan
        </div>
        <div class="card-body">
          @if($linkedThreads->isEmpty())
            <div class="text-center text-muted py-4">
              <i class="icon-base ti tabler-message icon-32px d-block mb-2 opacity-50"></i>
              <p class="mb-0">{{ __('No message threads linked to this consultation.') }}</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th>{{ __('Subject') }}</th>
                    <th>{{ __('Created') }}</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($linkedThreads as $thread)
                    <tr>
                      <td><a href="{{ route($clientRoutePrefix . '.threads.show', [$consultation->client, $thread]) }}">{{ $thread->subject }}</a></td>
                      <td class="text-muted small">{{ $thread->created_at?->format('Y-m-d H:i') }}</td>
                      <td>
                        @can('consultations.manage')
                        <form action="{{ route($consultationRoutePrefix . '.unlink-thread', [$consultation, $thread]) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Unlink this thread?') }}');">
                          @csrf
                          <button type="submit" class="btn btn-icon btn-sm btn-text-secondary rounded" title="{{ __('Unlink') }}">
                            <i class="icon-base ti tabler-link-off"></i>
                          </button>
                        </form>
                        @endcan
                        <a href="{{ route($clientRoutePrefix . '.threads.show', [$consultation->client, $thread]) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                          <i class="icon-base ti tabler-eye"></i>
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Create new thread for consultation --}}
@can('consultations.manage')
<div class="modal fade" id="createThreadModal" tabindex="-1" aria-labelledby="createThreadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route($consultationRoutePrefix . '.create-thread', $consultation) }}" method="post">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="createThreadModalLabel">{{ __('Create message thread for consultation') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="thread_subject" class="form-label">{{ __('Subject') }} <span class="text-danger">*</span></label>
            <input type="text" name="subject" id="thread_subject" class="form-control" value="{{ $consultation->title }}" maxlength="255" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endcan
@endsection
