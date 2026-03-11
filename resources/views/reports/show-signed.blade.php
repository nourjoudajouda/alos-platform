<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report->title }}</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 1em 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .meta { color: #666; font-size: 0.9em; margin-bottom: 1.5em; }
    </style>
</head>
<body>
    <h1>{{ $report->title }}</h1>
    <p class="meta">{{ __('Client') }}: {{ $report->client->name }} · {{ __('Generated at') }}: {{ $report->generated_at?->format('Y-m-d H:i') }}</p>

    @if(($payload['report_type'] ?? '') === 'case_status' && !empty($payload['cases']))
        <table>
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
    @elseif(($payload['report_type'] ?? '') === 'activity_summary')
        @if(!empty($payload['cases_updated']))
            <h3>{{ __('Cases updated') }}</h3>
            <ul>
                @foreach($payload['cases_updated'] as $c)
                    <li>{{ $c['case_number'] ?? '' }} — {{ $c['updated_at'] ?? '' }}</li>
                @endforeach
            </ul>
        @endif
        @if(!empty($payload['consultations_new_or_updated']))
            <h3>{{ __('Consultations') }}</h3>
            <ul>
                @foreach($payload['consultations_new_or_updated'] as $c)
                    <li>{{ $c['title'] ?? '' }} — {{ $c['updated_at'] ?? '' }}</li>
                @endforeach
            </ul>
        @endif
        <p>{{ __('New messages in period') }}: {{ $payload['new_messages_count'] ?? 0 }}</p>
        @if(!empty($payload['upcoming_sessions']))
            <h3>{{ __('Upcoming sessions') }}</h3>
            <ul>
                @foreach($payload['upcoming_sessions'] as $s)
                    <li>{{ $s['case_number'] ?? '' }} — {{ $s['session_date'] ?? '' }} {{ $s['session_time'] ?? '' }} — {{ $s['court_name'] ?? '' }}</li>
                @endforeach
            </ul>
        @endif
    @elseif(($payload['report_type'] ?? '') === 'new_documents' && !empty($payload['documents']))
        <table>
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
    @else
        <p>{{ __('No detailed data in this report.') }}</p>
    @endif
</body>
</html>
