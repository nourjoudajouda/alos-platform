@php
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $reportTypes = [
    \App\Models\GeneratedReport::TYPE_CASE_STATUS => __('Case Status'),
    \App\Models\GeneratedReport::TYPE_ACTIVITY_SUMMARY => __('Activity Summary'),
    \App\Models\GeneratedReport::TYPE_NEW_DOCUMENTS => __('New Documents'),
  ];
@endphp

@extends('portal::layouts.portal')

@section('title', Str::limit($report->title, 50) . ' — ' . __('Client Portal'))

@section('content')
  <div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <div>
        <h4 class="fw-bold mb-1">{{ $report->title }}</h4>
        <p class="text-muted small mb-0">
          {{ $reportTypes[$report->report_type] ?? $report->report_type }}
          · {{ __('Generated at') }} {{ $report->generated_at?->format('Y-m-d H:i') }}
          @if($report->period_start && $report->period_end)
            · {{ $report->period_start->format('Y-m-d') }} — {{ $report->period_end->format('Y-m-d') }}
          @endif
        </p>
      </div>
      <a href="{{ route('portal.reports.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to reports') }}</a>
    </div>

    <div class="card">
      <div class="card-body">
        @if(($payload['report_type'] ?? '') === 'case_status' && !empty($payload['cases']))
          <h6 class="mb-3">{{ __('Cases') }}</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead>
                <tr>
                  <th>{{ __('Case number') }}</th>
                  <th>{{ __('Type') }}</th>
                  <th>{{ __('Status') }}</th>
                  <th>{{ __('Responsible lawyer') }}</th>
                  <th>{{ __('Last update') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($payload['cases'] as $c)
                  <tr>
                    <td>{{ $c['case_number'] ?? '—' }}</td>
                    <td>{{ $c['case_type'] ?? '—' }}</td>
                    <td>{{ $c['status_label'] ?? $c['status'] ?? '—' }}</td>
                    <td>{{ $c['responsible_lawyer']['name'] ?? '—' }}</td>
                    <td>{{ isset($c['last_updated']) ? \Carbon\Carbon::parse($c['last_updated'])->format('Y-m-d H:i') : '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @elseif(($payload['report_type'] ?? '') === 'activity_summary')
          @if(!empty($payload['cases_updated']))
            <h6 class="mb-2">{{ __('Cases updated') }}</h6>
            <ul class="mb-3">
              @foreach($payload['cases_updated'] as $c)
                <li>{{ $c['case_number'] ?? '' }} — {{ isset($c['updated_at']) ? \Carbon\Carbon::parse($c['updated_at'])->format('Y-m-d H:i') : '' }}</li>
              @endforeach
            </ul>
          @endif
          @if(!empty($payload['consultations_new_or_updated']))
            <h6 class="mb-2">{{ __('Consultations') }}</h6>
            <ul class="mb-3">
              @foreach($payload['consultations_new_or_updated'] as $c)
                <li>{{ $c['title'] ?? '' }} — {{ isset($c['updated_at']) ? \Carbon\Carbon::parse($c['updated_at'])->format('Y-m-d H:i') : '' }}</li>
              @endforeach
            </ul>
          @endif
          <p><strong>{{ __('New messages in period') }}:</strong> {{ $payload['new_messages_count'] ?? 0 }}</p>
          @if(!empty($payload['upcoming_sessions']))
            <h6 class="mb-2">{{ __('Upcoming sessions') }}</h6>
            <ul>
              @foreach($payload['upcoming_sessions'] as $s)
                <li>{{ $s['case_number'] ?? '' }} — {{ $s['session_date'] ?? '' }} {{ $s['session_time'] ?? '' }} — {{ $s['court_name'] ?? '' }}</li>
              @endforeach
            </ul>
          @endif
        @elseif(($payload['report_type'] ?? '') === 'new_documents' && !empty($payload['documents']))
          <h6 class="mb-3">{{ __('Documents shared with you') }}</h6>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead>
                <tr>
                  <th>{{ __('Document') }}</th>
                  <th>{{ __('Shared at') }}</th>
                  <th>{{ __('Case / Consultation') }}</th>
                </tr>
              </thead>
              <tbody>
                @foreach($payload['documents'] as $d)
                  <tr>
                    <td>{{ $d['name'] ?? '—' }}</td>
                    <td>{{ isset($d['shared_at']) ? \Carbon\Carbon::parse($d['shared_at'])->format('Y-m-d H:i') : '—' }}</td>
                    <td>{{ $d['case_number'] ?? $d['consultation_title'] ?? '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <p class="text-muted mb-0">{{ __('No detailed data in this report.') }}</p>
        @endif
      </div>
    </div>
  </div>
@endsection
