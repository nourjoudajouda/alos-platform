@php
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $reportTypes = [
    \App\Models\GeneratedReport::TYPE_CASE_STATUS => __('Case Status'),
    \App\Models\GeneratedReport::TYPE_ACTIVITY_SUMMARY => __('Activity Summary'),
    \App\Models\GeneratedReport::TYPE_NEW_DOCUMENTS => __('New Documents'),
  ];
  $deliveryChannels = [
    \App\Models\ClientReportSetting::DELIVERY_IN_APP => __('In-app only'),
    \App\Models\ClientReportSetting::DELIVERY_EMAIL => __('Email only'),
    \App\Models\ClientReportSetting::DELIVERY_BOTH => __('In-app + Email'),
  ];
  $frequencies = [
    \App\Models\ClientReportSetting::FREQUENCY_WEEKLY => __('Weekly'),
    \App\Models\ClientReportSetting::FREQUENCY_MONTHLY => __('Monthly'),
    \App\Models\ClientReportSetting::FREQUENCY_MAJOR_UPDATE => __('On major update'),
  ];
@endphp
@extends('core::layouts.layoutMaster')

@section('title', __('Reports') . ' — ' . $client->name . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('admin.core.clients.show', $client) }}">{{ $client->name }}</a></li>
          <li class="breadcrumb-item active">{{ __('Reports') }}</li>
        </ol>
      </nav>
      <h4 class="fw-bold mb-1">{{ __('Report settings & generated reports') }}</h4>
      <p class="text-muted small mb-0">{{ __('Configure auto reports for :name and view sent reports.', ['name' => $client->name]) }}</p>
    </div>
    <a href="{{ route('admin.core.clients.show', [$client, 'tab' => 'overview']) }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to client') }}</a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  {{-- Report settings --}}
  @can('reports.manage')
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('Report settings') }}</h5>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.core.clients.reports.settings.update', $client) }}" method="post">
        @csrf
        @method('PUT')
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">{{ __('Report types') }}</label>
            <div class="d-flex flex-wrap gap-4">
              <div class="form-check">
                <input type="checkbox" name="case_status_enabled" id="case_status_enabled" value="1" class="form-check-input" {{ $settings->case_status_enabled ? 'checked' : '' }}>
                <label for="case_status_enabled" class="form-check-label">{{ __('Case status report') }}</label>
              </div>
              <div class="form-check">
                <input type="checkbox" name="activity_summary_enabled" id="activity_summary_enabled" value="1" class="form-check-input" {{ $settings->activity_summary_enabled ? 'checked' : '' }}>
                <label for="activity_summary_enabled" class="form-check-label">{{ __('Activity summary report') }}</label>
              </div>
              <div class="form-check">
                <input type="checkbox" name="new_documents_enabled" id="new_documents_enabled" value="1" class="form-check-input" {{ $settings->new_documents_enabled ? 'checked' : '' }}>
                <label for="new_documents_enabled" class="form-check-label">{{ __('New documents report') }}</label>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <label for="delivery_channel" class="form-label">{{ __('Delivery channel') }}</label>
            <select name="delivery_channel" id="delivery_channel" class="form-select">
              @foreach($deliveryChannels as $value => $label)
                <option value="{{ $value }}" {{ $settings->delivery_channel === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-6">
            <label for="frequency" class="form-label">{{ __('Frequency') }}</label>
            <select name="frequency" id="frequency" class="form-select">
              @foreach($frequencies as $value => $label)
                <option value="{{ $value }}" {{ $settings->frequency === $value ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('Send to') }}</label>
            <div class="d-flex flex-wrap gap-4">
              <div class="form-check">
                <input type="checkbox" name="send_to_client" id="send_to_client" value="1" class="form-check-input" {{ $settings->send_to_client ? 'checked' : '' }}>
                <label for="send_to_client" class="form-check-label">{{ __('Client') }}</label>
              </div>
              <div class="form-check">
                <input type="checkbox" name="send_to_responsible_lawyer" id="send_to_responsible_lawyer" value="1" class="form-check-input" {{ $settings->send_to_responsible_lawyer ? 'checked' : '' }}>
                <label for="send_to_responsible_lawyer" class="form-check-label">{{ __('Responsible lawyer') }}</label>
              </div>
              <div class="form-check">
                <input type="checkbox" name="send_to_office_management" id="send_to_office_management" value="1" class="form-check-input" {{ $settings->send_to_office_management ? 'checked' : '' }}>
                <label for="send_to_office_management" class="form-check-label">{{ __('Office management') }}</label>
              </div>
            </div>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">
              <i class="icon-base ti tabler-device-floppy {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Save settings') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
  @endcan

  {{-- Generated reports list --}}
  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('Generated reports') }}</h5>
    </div>
    <div class="card-body">
      @if($reports->isEmpty())
        <div class="text-center py-5 text-muted">
          <i class="icon-base ti tabler-file-report icon-32px d-block mb-3 opacity-50"></i>
          <p class="mb-0">{{ __('No reports generated yet. Reports are created automatically according to the schedule or on major updates.') }}</p>
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Generated at') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Channel') }}</th>
                <th class="text-nowrap" style="min-width: 6rem;">{{ __('Actions') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach($reports as $r)
                <tr>
                  <td><span class="badge bg-label-primary">{{ $reportTypes[$r->report_type] ?? $r->report_type }}</span></td>
                  <td><span class="text-muted small">{{ Str::limit($r->title, 50) }}</span></td>
                  <td>{{ $r->generated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                  <td>
                    @if($r->status === \App\Models\GeneratedReport::STATUS_SENT)
                      <span class="badge bg-label-success">{{ __('Sent') }}</span>
                    @elseif($r->status === \App\Models\GeneratedReport::STATUS_FAILED)
                      <span class="badge bg-label-danger">{{ __('Failed') }}</span>
                    @else
                      <span class="badge bg-label-secondary">{{ __('Generated') }}</span>
                    @endif
                  </td>
                  <td><span class="text-muted small">{{ $r->sent_at ? ($deliveryChannels[optional($client->reportSettings)->delivery_channel ?? \App\Models\ClientReportSetting::DELIVERY_BOTH] ?? '—') : '—' }}</span></td>
                  <td class="text-nowrap">
                    <a href="{{ route('admin.core.clients.reports.show', [$client, $r]) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                      <i class="icon-base ti tabler-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="d-flex justify-content-center mt-3">
          {{ $reports->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
