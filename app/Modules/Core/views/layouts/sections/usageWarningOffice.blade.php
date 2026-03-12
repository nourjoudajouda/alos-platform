@auth
  @isset($usageWarnings)
    @if (!empty($usageWarnings))
      <div class="alert alert-warning alert-dismissible mb-3" role="alert">
        <strong><i class="icon-base ti tabler-alert-triangle me-1"></i>{{ __('Usage warning') }}</strong>
        {{ __('You are approaching your plan limits.') }}
        <a href="{{ route('company.settings.subscription.show') }}" class="alert-link">{{ __('View subscription') }}</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
  @endisset
@endauth
