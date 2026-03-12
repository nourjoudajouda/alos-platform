@php
  $crudIndexId = 'subscription-plans';
  $crudIndexTitle = __('Subscription Plans') . ' — ' . config('app.name');
  $crudIndexFiltersAction = route('admin.core.subscription-plans.index');
  $crudIndexPerPage = $perPage;
  $crudIndexTableTitle = __('Subscription Plans');
  $crudIndexAddUrl = route('admin.core.subscription-plans.create');
  $crudIndexAddLabel = __('Add Plan');
  $crudIndexEmptyMessage = __('No subscription plans yet.');
  $crudIndexEmptyLink = route('admin.core.subscription-plans.create');
  $crudIndexEmptyLinkText = __('Add Plan');
  $crudIndexEmptyColspan = 6;
  $crudIndexShowViewToggle = false;
  $items = $plans;
  $configData = Helper::appClasses();
  $contentDir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
@extends('core::layouts.crud-index-layout')

@section('crud_description')
  {{ __('Manage subscription plans. Each plan defines user limit, lawyer limit, and storage limit. Assign a plan to a tenant from the tenant edit page.') }}
@endsection

@section('crud_stats')
  @include('core::_partials.crud-stat-card', ['title' => __('Total Plans'), 'value' => $totalPlans ?? 0, 'subtitle' => __('Plans'), 'icon' => 'ti tabler-credit-card', 'bgLabel' => 'primary'])
@endsection

@section('crud_table_header')
  <th>{{ __('Plan name') }}</th>
  <th>{{ __('Price') }}</th>
  <th>{{ __('User limit') }}</th>
  <th>{{ __('Lawyer limit') }}</th>
  <th>{{ __('Storage limit (MB)') }}</th>
  <th class="text-nowrap" style="min-width: 7rem;">{{ __('Actions') }}</th>
@endsection

@section('crud_table_body')
  @forelse($plans as $plan)
    <tr>
      <td><strong>{{ $plan->plan_name }}</strong></td>
      <td>{{ number_format($plan->price, 2) }}</td>
      <td>{{ $plan->user_limit }}</td>
      <td>{{ $plan->lawyer_limit }}</td>
      <td>{{ $plan->storage_limit }}</td>
      <td class="text-nowrap">
        <div class="table-actions">
          <a href="{{ route('admin.core.subscription-plans.edit', $plan) }}" class="btn btn-icon btn-sm btn-text-warning rounded" title="{{ __('Edit') }}">
            <i class="icon-base ti tabler-pencil"></i>
          </a>
          <form action="{{ route('admin.core.subscription-plans.destroy', $plan) }}" method="post" class="d-inline" onsubmit="return confirm('{{ __('Delete this plan? Tenants must not be assigned to it.') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-icon btn-sm btn-text-danger rounded" title="{{ __('Delete') }}">
              <i class="icon-base ti tabler-trash"></i>
            </button>
          </form>
        </div>
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="{{ $crudIndexEmptyColspan }}" class="text-center text-muted py-5">
        <i class="icon-base ti tabler-credit-card icon-32px d-block mb-2 opacity-50"></i>
        {{ $crudIndexEmptyMessage }} <a href="{{ $crudIndexEmptyLink }}">{{ $crudIndexEmptyLinkText }}</a>
      </td>
    </tr>
  @endforelse
@endsection
