@php
  $configData = Helper::appClasses();
  $clientRoutePrefix = $clientRoutePrefix ?? 'admin.core.clients';
  $initials = strtoupper(mb_substr(preg_replace('/[^a-zA-Z0-9\p{Arabic}]/u', '', $client->name), 0, 2) ?: 'CL');
  if (mb_strlen($initials) > 2) $initials = mb_substr($initials, 0, 2);
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
  $activeTab = request('tab', 'overview');
  $tabs = ['overview', 'cases', 'consultations', 'messages', 'documents', 'reports', 'team-access', 'portal'];
  if (!in_array($activeTab, $tabs, true)) $activeTab = 'overview';
  $portalUser = $client->portalUser;
  $userHasClientAccess = $userHasClientAccess ?? false;
  $clientCases = $clientCases ?? collect();
  $clientConsultations = $clientConsultations ?? collect();
  $portalPermissions = [
    \App\Models\User::PORTAL_PERMISSION_VIEW_ONLY => __('View only'),
    \App\Models\User::PORTAL_PERMISSION_MESSAGING => __('View + messaging'),
    \App\Models\User::PORTAL_PERMISSION_MESSAGING_UPLOAD => __('View + messaging + upload'),
  ];
@endphp
@extends('core::layouts.layoutMaster')

@section('title', $client->name . ' — ' . config('app.name'))

@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y" dir="{{ $contentDir }}">
  {{-- Client profile header --}}
  <div class="card mb-4">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
          <span class="avatar avatar-xl">
            <span class="avatar-initial rounded bg-label-primary">{{ $initials }}</span>
          </span>
          <div>
            <h4 class="mb-1">{{ $client->name }}</h4>
            @if($client->email)
              <p class="text-muted mb-1 small">{{ $client->email }}</p>
            @endif
            @if($client->tenant)
              <span class="badge bg-label-secondary">{{ $client->tenant->name }}</span>
            @endif
            <span class="text-muted small ms-2">{{ __('Created') }} {{ $client->created_at?->format('Y-m-d') }}</span>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route($clientRoutePrefix . '.edit', $client) }}" class="btn btn-warning btn-sm">
            <i class="icon-base ti tabler-pencil {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Edit') }}
          </a>
          <a href="{{ route($clientRoutePrefix . '.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Back to list') }}</a>
        </div>
      </div>
    </div>
  </div>

  {{-- Profile tabs: Overview | Cases | Consultations | Messages | Documents | Team Access --}}
  <ul class="nav nav-tabs nav-fill mb-3" id="clientProfileTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'overview']) }}" role="tab">{{ __('Overview') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'cases' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'cases']) }}" role="tab">{{ __('Cases') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'consultations' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'consultations']) }}" role="tab">{{ __('Consultations') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'messages' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'messages']) }}" role="tab">{{ __('Messages') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'documents']) }}" role="tab">{{ __('Documents') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'reports' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.reports.index', $client) }}" role="tab">{{ __('Reports') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'team-access' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'team-access']) }}" role="tab">{{ __('Team Access') }}</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link {{ $activeTab === 'portal' ? 'active' : '' }}" href="{{ route($clientRoutePrefix . '.show', ['client' => $client, 'tab' => 'portal']) }}" role="tab">{{ __('Portal') }}</a>
    </li>
  </ul>

  <div class="tab-content" id="clientProfileTabContent">
    {{-- Overview --}}
    <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('Overview') }}</h5>
        </div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-3">{{ __('Name') }}</dt>
            <dd class="col-sm-9">{{ $client->name }}</dd>
            <dt class="col-sm-3">{{ __('Email') }}</dt>
            <dd class="col-sm-9">{{ $client->email ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Phone') }}</dt>
            <dd class="col-sm-9">{{ $client->phone ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Tenant') }}</dt>
            <dd class="col-sm-9">{{ $client->tenant?->name ?? '—' }}</dd>
            <dt class="col-sm-3">{{ __('Created') }}</dt>
            <dd class="col-sm-9">{{ $client->created_at?->format('Y-m-d H:i') }}</dd>
          </dl>
        </div>
      </div>
    </div>

    {{-- Cases — only visible to users with client team access --}}
    <div class="tab-pane fade {{ $activeTab === 'cases' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Cases') }}</h5>
          @if($userHasClientAccess && auth()->user()->can('cases.manage'))
            <a href="{{ route('admin.core.cases.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm">
              <i class="icon-base ti tabler-plus {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Add Case') }}
            </a>
          @endif
        </div>
        <div class="card-body">
          @if(!$userHasClientAccess)
            <div class="text-center py-5 text-muted">
              <i class="icon-base ti tabler-lock icon-32px d-block mb-3 opacity-50"></i>
              <p class="mb-0">{{ __('You do not have access to this client. Only team members assigned to this client can view and manage cases.') }}</p>
            </div>
          @elseif($clientCases->isEmpty())
            <div class="text-center py-5 text-muted">
              <i class="icon-base ti tabler-briefcase icon-32px d-block mb-3 opacity-50"></i>
              <p class="mb-2">{{ __('No cases yet.') }}</p>
              @if(auth()->user()->can('cases.manage'))
                <a href="{{ route('admin.core.cases.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm">{{ __('Add Case') }}</a>
              @endif
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>{{ __('Case number') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Responsible lawyer') }}</th>
                    <th class="text-nowrap" style="min-width: 6rem;">{{ __('Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($clientCases as $c)
                    @php
                      $sc = match($c->status) { 'open' => 'primary', 'pending' => 'warning', 'closed' => 'secondary', default => 'secondary' };
                    @endphp
                    <tr>
                      <td><a href="{{ route('admin.core.cases.show', $c) }}" class="fw-medium">{{ $c->case_number }}</a></td>
                      <td><span class="text-muted small">{{ $c->case_type ?? '—' }}</span></td>
                      <td><span class="badge bg-label-{{ $sc }}">{{ __(\App\Models\CaseModel::STATUSES[$c->status] ?? $c->status) }}</span></td>
                      <td><span class="text-muted small">{{ $c->responsibleLawyer?->name ?? '—' }}</span></td>
                      <td class="text-nowrap">
                        <a href="{{ route('admin.core.cases.show', $c) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                          <i class="icon-base ti tabler-eye"></i>
                        </a>
                        @can('cases.manage')
                        <a href="{{ route('admin.core.cases.edit', $c) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
                          <i class="icon-base ti tabler-pencil"></i>
                        </a>
                        @endcan
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

    {{-- Consultations — ALOS-S1-14 --}}
    <div class="tab-pane fade {{ $activeTab === 'consultations' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Consultations') }}</h5>
          @if($userHasClientAccess && auth()->user()->can('consultations.manage'))
            <a href="{{ route('admin.core.consultations.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm">
              <i class="icon-base ti tabler-plus {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
              {{ __('Add Consultation') }}
            </a>
          @endif
        </div>
        <div class="card-body">
          @if(!$userHasClientAccess || !auth()->user()->can('consultations.view'))
            <div class="text-center py-5 text-muted">
              <i class="icon-base ti tabler-lock icon-32px d-block mb-3 opacity-50"></i>
              <p class="mb-0">{{ __('You do not have access to consultations for this client.') }}</p>
            </div>
          @elseif($clientConsultations->isEmpty())
            <div class="text-center py-5 text-muted">
              <i class="icon-base ti tabler-calendar-event icon-32px d-block mb-3 opacity-50"></i>
              <p class="mb-2">{{ __('No consultations yet.') }}</p>
              @if(auth()->user()->can('consultations.manage'))
                <a href="{{ route('admin.core.consultations.create', ['client_id' => $client->id]) }}" class="btn btn-primary btn-sm">{{ __('Add Consultation') }}</a>
              @endif
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Responsible') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-nowrap" style="min-width: 6rem;">{{ __('Actions') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($clientConsultations as $c)
                    @php
                      $sc = match($c->status) { 'open' => 'primary', 'completed' => 'success', 'archived' => 'secondary', default => 'secondary' };
                    @endphp
                    <tr>
                      <td><a href="{{ route('admin.core.consultations.show', $c) }}" class="fw-medium">{{ $c->title }}</a></td>
                      <td><span class="text-muted small">{{ $c->consultation_date?->format('Y-m-d') ?? '—' }}</span></td>
                      <td><span class="text-muted small">{{ $c->responsibleUser?->name ?? '—' }}</span></td>
                      <td><span class="badge bg-label-{{ $sc }}">{{ __(\App\Models\Consultation::STATUSES[$c->status] ?? $c->status) }}</span></td>
                      <td class="text-nowrap">
                        <a href="{{ route('admin.core.consultations.show', $c) }}" class="btn btn-icon btn-sm btn-text-primary rounded" title="{{ __('View') }}">
                          <i class="icon-base ti tabler-eye"></i>
                        </a>
                        @can('consultations.manage')
                        <a href="{{ route('admin.core.consultations.edit', $c) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
                          <i class="icon-base ti tabler-pencil"></i>
                        </a>
                        @endcan
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

    {{-- Messages — ALOS-S1-09 Secure Messaging --}}
    <div class="tab-pane fade {{ $activeTab === 'messages' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Messages') }}</h5>
          <a href="{{ route($clientRoutePrefix . '.threads.index', $client) }}" class="btn btn-primary btn-sm">
            <i class="icon-base ti tabler-message {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('View conversations') }}
          </a>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-0">
            {{ __('Secure messaging between the office and this client. Only team members with access to this client can view and reply.') }}
          </p>
        </div>
      </div>
    </div>

    {{-- Documents — ALOS-S1-10 Document Center --}}
    <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">{{ __('Documents') }}</h5>
          <a href="{{ route($clientRoutePrefix . '.documents.index', $client) }}" class="btn btn-primary btn-sm">
            <i class="icon-base ti tabler-folder {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
            {{ __('Open Document Center') }}
          </a>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-0">
            {{ __('Upload documents and choose visibility: internal (office only) or shared with the client. The client sees only shared documents in their portal.') }}
          </p>
        </div>
      </div>
    </div>

    {{-- Team Access — ALOS-S1-07: Lead Lawyer + Assigned Users --}}
    <div class="tab-pane fade {{ $activeTab === 'team-access' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="icon-base ti tabler-lock-access"></i>
          <span>{{ __('Team Access') }}</span>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-4">
            {{ __('Team Access controls which team members can view and manage this client. Set a lead lawyer and assign additional users.') }}
          </p>
          @if($assignableUsers->isEmpty())
            <div class="alert alert-warning mb-0">
              {{ __('No users in this office. Add internal users to the tenant first, then assign them here.') }}
            </div>
          @else
            <form action="{{ route($clientRoutePrefix . '.team-access.update', $client) }}" method="post">
              @csrf
              @method('PUT')
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="lead_lawyer_id" class="form-label">{{ __('Lead Lawyer') }}</label>
                  @php $selectedLeadId = (int) (old('lead_lawyer_id', $leadLawyer?->id)); @endphp
                  <select name="lead_lawyer_id" id="lead_lawyer_id" class="form-select select2-team-access">
                    <option value="">{{ __('None') }}</option>
                    @foreach($assignableUsers as $u)
                      <option value="{{ $u->id }}" {{ $selectedLeadId === (int) $u->id ? 'selected' : '' }}>{{ $u->name }} @if($u->email)({{ $u->email }})@endif</option>
                    @endforeach
                  </select>
                  <div class="form-text small">{{ __('The main lawyer responsible for this client.') }}</div>
                </div>
                <div class="col-md-6">
                  <label for="assigned_user_ids" class="form-label">{{ __('Assigned Users') }}</label>
                  <select name="assigned_user_ids[]" id="assigned_user_ids" class="form-select select2-team-access" multiple>
                    @foreach($assignableUsers as $u)
                      <option value="{{ $u->id }}" {{ in_array($u->id, old('assigned_user_ids', $assignedUserIds ?? []), true) ? 'selected' : '' }}>{{ $u->name }} @if($u->email)({{ $u->email }})@endif</option>
                    @endforeach
                  </select>
                  <div class="form-text small">{{ __('Search and select multiple users who can access this client.') }}</div>
                </div>
              </div>
              <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="icon-base ti tabler-device-floppy {{ $contentDir === 'rtl' ? 'ms-1' : 'me-1' }}"></i>
                  {{ __('Save Team Access') }}
                </button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>

    {{-- Portal — ALOS-S1-08: Create/Edit portal account + status --}}
    <div class="tab-pane fade {{ $activeTab === 'portal' ? 'show active' : '' }}" role="tabpanel">
      <div class="card">
        <div class="card-header d-flex align-items-center gap-2">
          <i class="icon-base ti tabler-user-circle"></i>
          <span>{{ __('Client Portal Account') }}</span>
        </div>
        <div class="card-body">
          <p class="text-muted small mb-4">
            {{ __('Create or manage the client portal login. The client can sign in at :url to view their data.', ['url' => url('/portal/login')]) }}
          </p>
          <p class="alert alert-info py-2 small mb-4">
            {{ __('Portal password is set by staff when creating the account. You can choose a password and share it with the client, or use “Generate temporary password” below.') }}
          </p>
          @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif
          @if (!$portalUser)
            <form action="{{ route($clientRoutePrefix . '.portal-user.store', $client) }}" method="post" id="portal-create-form">
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="portal_name" class="form-label">{{ __('Name') }}</label>
                  <input type="text" name="name" id="portal_name" class="form-control" value="{{ old('name', $client->name) }}" required />
                </div>
                <div class="col-md-6">
                  <label for="portal_email" class="form-label">{{ __('Email') }}</label>
                  <input type="email" name="email" id="portal_email" class="form-control" value="{{ old('email', $client->email) }}" required />
                </div>
                <div class="col-md-6">
                  <label for="portal_password" class="form-label">{{ __('Password') }}</label>
                  <div class="input-group">
                    <input type="text" name="password" id="portal_password" class="form-control" minlength="8" required autocomplete="off" />
                    <button type="button" class="btn btn-outline-secondary" id="portal_gen_password" title="{{ __('Generate temporary password') }}"><i class="icon-base ti tabler-key"></i></button>
                  </div>
                  <div class="form-text small">{{ __('Share this password with the client so they can sign in at :url', ['url' => url('/portal/login')]) }}</div>
                </div>
                <div class="col-md-6">
                  <label for="portal_password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                  <input type="text" name="password_confirmation" id="portal_password_confirmation" class="form-control" autocomplete="off" />
                </div>
                <div class="col-md-6">
                  <label for="portal_permission" class="form-label">{{ __('Portal permission') }}</label>
                  <select name="portal_permission" id="portal_permission" class="form-select" required>
                    @foreach($portalPermissions as $value => $label)
                      <option value="{{ $value }}" {{ old('portal_permission') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <div class="form-check">
                    <input type="checkbox" name="portal_active" id="portal_active" value="1" class="form-check-input" {{ old('portal_active', true) ? 'checked' : '' }} />
                    <label for="portal_active" class="form-check-label">{{ __('Account active') }}</label>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <button type="submit" class="btn btn-primary">{{ __('Create portal account') }}</button>
              </div>
            </form>
          @else
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
              <div>
                <strong>{{ $portalUser->name }}</strong> ({{ $portalUser->email }})
                <span class="badge {{ $portalUser->portal_active ? 'bg-label-success' : 'bg-label-secondary' }} ms-2">
                  {{ $portalUser->portal_active ? __('Active') : __('Inactive') }}
                </span>
                <span class="text-muted small ms-2">{{ __('Permission') }}: {{ $portalPermissions[$portalUser->portal_permission] ?? $portalUser->portal_permission }}</span>
                <div class="form-text small mt-1">{{ __('Summary above shows saved data. Change the form below and click Update to save.') }}</div>
              </div>
              <form action="{{ route($clientRoutePrefix . '.portal-user.toggle', $client) }}" method="post" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm {{ $portalUser->portal_active ? 'btn-warning' : 'btn-success' }}">
                  {{ $portalUser->portal_active ? __('Disable account') : __('Enable account') }}
                </button>
              </form>
            </div>
            <form action="{{ route($clientRoutePrefix . '.portal-user.update', $client) }}" method="post" id="portal-update-form">
              @csrf
              @method('PUT')
              @if ($errors->any())
                <div class="alert alert-danger mb-4">
                  <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $err)
                      <li>{{ $err }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="portal_edit_name" class="form-label">{{ __('Name') }}</label>
                  <input type="text" name="name" id="portal_edit_name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name', $portalUser->name) }}" required />
                  @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label for="portal_edit_email" class="form-label">{{ __('Email') }}</label>
                  <input type="email" name="email" id="portal_edit_email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $portalUser->email) }}" required />
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label for="portal_edit_password" class="form-label">{{ __('New password') }}</label>
                  <input type="password" name="password" id="portal_edit_password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" minlength="8" autocomplete="new-password" />
                  <div class="form-text small">{{ __('Leave blank to keep current password.') }}</div>
                  @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label for="portal_edit_password_confirmation" class="form-label">{{ __('Confirm new password') }}</label>
                  <input type="password" name="password_confirmation" id="portal_edit_password_confirmation" class="form-control" autocomplete="new-password" />
                </div>
                <div class="col-md-6">
                  <label for="portal_edit_permission" class="form-label">{{ __('Portal permission') }}</label>
                  <select name="portal_permission" id="portal_edit_permission" class="form-select {{ $errors->has('portal_permission') ? 'is-invalid' : '' }}" required>
                    @foreach($portalPermissions as $value => $label)
                      <option value="{{ $value }}" {{ old('portal_permission', $portalUser->portal_permission) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                  <div class="form-text small">{{ __('Choose from the list; then click Update to save.') }}</div>
                  @error('portal_permission')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 d-flex align-items-end">
                  <input type="hidden" name="portal_active" value="0" />
                  <div class="form-check">
                    <input type="checkbox" name="portal_active" id="portal_edit_active" value="1" class="form-check-input" {{ old('portal_active', $portalUser->portal_active) ? 'checked' : '' }} />
                    <label for="portal_edit_active" class="form-check-label">{{ __('Account active') }}</label>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <button type="submit" class="btn btn-primary">{{ __('Update portal account') }}</button>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
(function () {
  'use strict';
  // Select2 — Lead Lawyer & Assigned Users (Team Access tab)
  var $teamSelects = $('.select2-team-access');
  if (typeof $ !== 'undefined' && $teamSelects.length) {
    $teamSelects.each(function () {
      var $this = $(this);
      if (!$this.hasClass('select2-hidden-accessible')) {
        $this.wrap('<div class="position-relative"></div>').select2({
          dropdownParent: $this.parent(),
          placeholder: $this.prop('multiple') ? '{{ __("Search and select users…") }}' : null,
          allowClear: !$this.prop('multiple'),
          width: '100%'
        });
      }
    });
  }
  // Portal: generate temporary password
  var genBtn = document.getElementById('portal_gen_password');
  if (genBtn) {
    genBtn.addEventListener('click', function () {
      var chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
      var pass = '';
      for (var i = 0; i < 12; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
      var el = document.getElementById('portal_password');
      var el2 = document.getElementById('portal_password_confirmation');
      if (el) { el.value = pass; }
      if (el2) { el2.value = pass; }
    });
  }
})();
</script>
@endsection
