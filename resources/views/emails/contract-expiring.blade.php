<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Subscription expiring soon') }}</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2 style="color: #1a1a1a;">{{ __('Subscription expiring soon') }}</h2>
    <p>{{ __('Your law firm subscription on :app is approaching its end date. Please renew to avoid service interruption.', ['app' => config('app.name')]) }}</p>
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>{{ __('Company') }}</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $tenant->name }}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>{{ __('Plan') }}</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $tenant->subscriptionPlan?->plan_name ?? __('N/A') }}</td></tr>
        <tr><td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>{{ __('Expiration date') }}</strong></td><td style="padding: 8px 0; border-bottom: 1px solid #eee;">{{ $tenant->contract_end_date?->format('Y-m-d') }}</td></tr>
    </table>
    <p>{{ __('To renew your subscription, please contact your platform administrator.') }}</p>
    <p style="font-size: 12px; color: #666;">{{ config('app.name') }}</p>
</body>
</html>
