<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report->title }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #1a1a1a;">{{ $report->title }}</h2>
    <p>{{ __('A new report has been generated for you in ALOS.') }}</p>
    @php
        $payload = $report->getPayload();
        $summary = $payload['summary'] ?? [];
    @endphp
    @if(!empty($summary))
        <p><strong>{{ __('Summary') }}:</strong></p>
        <ul>
            @foreach($summary as $key => $value)
                <li>{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_array($value) ? json_encode($value) : $value }}</li>
            @endforeach
        </ul>
    @endif
    <p>
        <a href="{{ $reportUrl ?? url('/') }}" style="display: inline-block; padding: 10px 20px; background: #696cff; color: #fff; text-decoration: none; border-radius: 6px;">{{ __('View report in ALOS') }}</a>
    </p>
    <p style="font-size: 12px; color: #666;">{{ __('Generated at') }}: {{ $report->generated_at?->format('Y-m-d H:i') }}</p>
</body>
</html>
