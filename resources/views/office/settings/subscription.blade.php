@extends('core::layouts.layoutMaster')

@section('title', __('Subscription Details') . ' — ' . ($tenant->name ?? config('app.name')))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Subscription Details') }}</h4>
      <p class="text-body mb-0 small">{{ __('Your plan limits and usage. View only — to change plan contact your administrator.') }}</p>
    </div>
    <a href="{{ route('company.dashboard') }}" class="btn btn-outline-secondary btn-sm">
      <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back to Dashboard') }}
    </a>
  </div>

  @if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (!$canWrite)
    <div class="alert alert-warning mb-4" role="alert">
      <strong>{{ __('Subscription expired or suspended') }}</strong>
      {{ __('You can view data but cannot add new users, upload documents, or send messages. Please renew your subscription.') }}
    </div>
  @endif

  @if (!empty($warnings))
    <div class="alert alert-warning mb-4" role="alert">
      <strong><i class="icon-base ti tabler-alert-triangle me-1"></i>{{ __('Usage warning') }}</strong>
      <p class="mb-0 mt-1">{{ __('You are approaching your plan limits (90% or more). Consider upgrading.') }}</p>
      <ul class="mb-0 mt-2">
        @if (!empty($warnings['users']))
          <li>{{ __('Users') }}: {{ $warnings['users']['current'] }} / {{ $warnings['users']['limit'] }} ({{ $warnings['users']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['lawyers']))
          <li>{{ __('Lawyers') }}: {{ $warnings['lawyers']['current'] }} / {{ $warnings['lawyers']['limit'] }} ({{ $warnings['lawyers']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['storage']))
          <li>{{ __('Storage') }}: {{ number_format($warnings['storage']['current_mb'], 1) }} MB / {{ $warnings['storage']['limit_mb'] }} MB ({{ $warnings['storage']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['clients']))
          <li>{{ __('Clients') }}: {{ $warnings['clients']['current'] }} / {{ $warnings['clients']['limit'] }} ({{ $warnings['clients']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['cases']))
          <li>{{ __('Cases') }}: {{ $warnings['cases']['current'] }} / {{ $warnings['cases']['limit'] }} ({{ $warnings['cases']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['accountants']))
          <li>{{ __('Accountants') }}: {{ $warnings['accountants']['current'] }} / {{ $warnings['accountants']['limit'] }} ({{ $warnings['accountants']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['trainees']))
          <li>{{ __('Trainees') }}: {{ $warnings['trainees']['current'] }} / {{ $warnings['trainees']['limit'] }} ({{ $warnings['trainees']['percent'] }}%)</li>
        @endif
        @if (!empty($warnings['documents']))
          <li>{{ __('Documents') }}: {{ $warnings['documents']['current'] }} / {{ $warnings['documents']['limit'] }} ({{ $warnings['documents']['percent'] }}%)</li>
        @endif
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <h5 class="card-title mb-4">{{ __('Plan & subscription') }}</h5>

      <div class="row g-3">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Plan name') }}</span>
            <strong>{{ $planName }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Subscription status') }}</span>
            <span class="badge bg-{{ $subscriptionStatus === 'active' || $subscriptionStatus === 'trial' ? 'success' : ($subscriptionStatus === 'expired' ? 'danger' : 'warning') }}">
              {{ ucfirst($subscriptionStatus ?? '—') }}
            </span>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Contract start date') }}</span>
            <strong>{{ $contractStartDate ? (\Carbon\Carbon::parse($contractStartDate)->format('Y-m-d')) : '—' }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Contract end date') }}</span>
            <strong>{{ $contractEndDate ? (\Carbon\Carbon::parse($contractEndDate)->format('Y-m-d')) : '—' }}</strong>
          </div>
        </div>
        @if ($billingCycle || $planPrice !== null)
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Billing cycle') }}</span>
            <strong>{{ $billingCycle ? ucfirst($billingCycle) : '—' }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Plan price') }}</span>
            <strong>{{ $planPrice !== null ? number_format($planPrice, 2) : '—' }}</strong>
          </div>
        </div>
        @endif
      </div>

      <h6 class="card-title mt-4 mb-3">{{ __('Usage metrics') }}</h6>
      <div class="row g-3">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Users (current / limit)') }}</span>
            <strong>{{ $userCount }} / {{ $userLimit <= 0 ? __('Unlimited') : $userLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Lawyers') }}</span>
            <strong>{{ $lawyerCount }} / {{ $lawyerLimit <= 0 ? __('Unlimited') : $lawyerLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Admins') }}</span>
            <strong>{{ $adminCount }} / {{ $adminLimit <= 0 ? __('Unlimited') : $adminLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Secretaries') }}</span>
            <strong>{{ $secretaryCount }} / {{ $secretaryLimit <= 0 ? __('Unlimited') : $secretaryLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Accountants') }}</span>
            <strong>{{ $accountantCount }} / {{ $accountantLimit <= 0 ? __('Unlimited') : $accountantLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Trainees') }}</span>
            <strong>{{ $traineeCount }} / {{ $traineeLimit <= 0 ? __('Unlimited') : $traineeLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Storage (GB)') }}</span>
            <strong>{{ number_format($storageUsedGb, 2) }} / {{ $storageLimitMb <= 0 ? __('Unlimited') : number_format($storageLimitGb, 2) }} GB</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Clients') }}</span>
            <strong>{{ $clientCount }} / {{ $clientLimit <= 0 ? __('Unlimited') : $clientLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Cases') }}</span>
            <strong>{{ $caseCount }} / {{ $caseLimit <= 0 ? __('Unlimited') : $caseLimit }}</strong>
          </div>
        </div>
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Max Documents') }}</span>
            <strong>{{ $documentCount }} / {{ $documentLimit <= 0 ? __('Unlimited') : $documentLimit }}</strong>
          </div>
        </div>
      </div>

      <p class="text-muted small mt-4 mb-0">
        {{ __('To change your plan or renew, please contact your administrator.') }}
      </p>
    </div>
  </div>
</div>
@endsection
