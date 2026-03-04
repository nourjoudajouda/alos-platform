@extends('core::layouts.layoutMaster')

@section('title', __('Dashboard') . ' — ' . config('app.name'))

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  {{-- عنوان الصفحة وترحيب (بنفس أسلوب Advocate SaaS) --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
      <h4 class="fw-bold mb-1">{{ __('Dashboard') }}</h4>
      <p class="text-body mb-0 small">{{ __('Overview of practice and quick follow-up') }}</p>
    </div>
  </div>

  {{-- صف كروت الإحصائيات (Case statistics, Invoices, Payments, Clients) --}}
  <div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Total Cases') }}</p>
            <h3 class="mb-0 fw-bold">0</h3>
            <span class="badge bg-label-success mt-1">{{ __('Ready to connect') }}</span>
            <p class="mb-0 mt-2 small text-muted">{{ __('Case status and follow-up') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-briefcase icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Active Cases') }}</p>
            <h3 class="mb-0 fw-bold">0</h3>
            <span class="badge bg-label-info mt-1">{{ __('In progress') }}</span>
            <p class="mb-0 mt-2 small text-muted">{{ __('Open cases currently') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base ti tabler-folder-open icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Invoices') }}</p>
            <h3 class="mb-0 fw-bold">0</h3>
            <span class="badge bg-label-warning mt-1">{{ __('Coming soon') }}</span>
            <p class="mb-0 mt-2 small text-muted">{{ __('Invoices and payments') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base ti tabler-file-invoice icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card h-100">
        <div class="card-body d-flex align-items-start justify-content-between">
          <div class="me-3">
            <p class="card-title mb-1 text-muted small">{{ __('Clients') }}</p>
            <h3 class="mb-0 fw-bold">0</h3>
            <span class="badge bg-label-secondary mt-1">{{ __('Coming soon') }}</span>
            <p class="mb-0 mt-2 small text-muted">{{ __('Client records') }}</p>
          </div>
          <div class="avatar flex-shrink-0">
            <span class="avatar-initial rounded bg-label-secondary">
              <i class="icon-base ti tabler-users icon-24px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- صف التصور + الملخص المالي (بنفس فكرة Advocate: Real-time visualization + Financial summary) --}}
  <div class="row g-4 mb-4">
    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Activity overview') }}</h5>
          <span class="badge bg-label-primary">{{ __('Coming soon: charts') }}</span>
        </div>
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-center rounded border border-dashed border-2 py-5" style="min-height: 220px;">
            <div class="text-center text-muted">
              <i class="icon-base ti tabler-chart-line icon-48px d-block mb-2 opacity-50"></i>
              <p class="mb-0 small">{{ __('Charts and case data will appear here') }}</p>
              <p class="mb-0 small mt-1">({{ __('Case tracking & productivity metrics') }})</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Financial summary') }}</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Total invoices') }}</span>
            <span class="fw-semibold">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">{{ __('Paid') }}</span>
            <span class="fw-semibold text-success">—</span>
          </div>
          <div class="d-flex justify-content-between align-items-center py-2">
            <span class="text-muted">{{ __('Remaining') }}</span>
            <span class="fw-semibold text-warning">—</span>
          </div>
          <p class="small text-muted mt-3 mb-0">{{ __('Billing and payment status will appear here') }}</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent activities (بنفس تفاصيل Advocate: Recent activity card) --}}
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Recent activities') }}</h5>
          <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>{{ __('Activity') }}</th>
                  <th>{{ __('Date') }}</th>
                  <th>{{ __('Status') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="3" class="text-center text-muted py-5">
                    <i class="icon-base ti tabler-activity icon-32px d-block mb-2 opacity-50"></i>
                    {{ __('No recent activities. Case, appointment and payment updates will appear here.') }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
