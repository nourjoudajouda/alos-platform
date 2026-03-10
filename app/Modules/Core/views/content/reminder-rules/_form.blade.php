@props(['rule' => null, 'action' => '', 'method' => 'POST', 'submitLabel' => __('Save')])
<form action="{{ $action }}" method="post">
  @csrf
  @if($method !== 'POST')
    @method($method)
  @endif
  <div class="row">
    <div class="col-md-8">
      <div class="mb-3">
        <label for="label" class="form-label">{{ __('Label') }} <span class="text-danger">*</span></label>
        <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $rule?->label) }}" required maxlength="64" placeholder="{{ __('e.g. 7 days before session') }}">
        @error('label')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-3">
        <label for="trigger_minutes" class="form-label">{{ __('Trigger (minutes before session)') }} <span class="text-danger">*</span></label>
        <input type="number" name="trigger_minutes" id="trigger_minutes" class="form-control @error('trigger_minutes') is-invalid @enderror" value="{{ old('trigger_minutes', $rule?->trigger_minutes ?? 1440) }}" required min="1" max="525600">
        <div class="form-text">{{ __('Examples: 10080 = 7 days, 1440 = 24 hours, 120 = 2 hours') }}</div>
        @error('trigger_minutes')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-3">
        <label for="sort_order" class="form-label">{{ __('Sort order') }}</label>
        <input type="number" name="sort_order" id="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $rule?->sort_order ?? 0) }}" min="0">
        @error('sort_order')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-light border-0 mb-3">
        <div class="card-body">
          <label class="form-label">{{ __('Channels') }}</label>
          <div class="form-check">
            <input type="checkbox" name="channel_database" id="channel_database" value="1" class="form-check-input" {{ old('channel_database', $rule?->channel_database ?? true) ? 'checked' : '' }}>
            <label for="channel_database" class="form-check-label">{{ __('In-app notification') }}</label>
          </div>
          <div class="form-check">
            <input type="checkbox" name="channel_mail" id="channel_mail" value="1" class="form-check-input" {{ old('channel_mail', $rule?->channel_mail ?? true) ? 'checked' : '' }}>
            <label for="channel_mail" class="form-check-label">{{ __('Email') }}</label>
          </div>
        </div>
      </div>
      <div class="mb-3">
        <div class="form-check">
          <input type="checkbox" name="notify_client" id="notify_client" value="1" class="form-check-input" {{ old('notify_client', $rule?->notify_client ?? false) ? 'checked' : '' }}>
          <label for="notify_client" class="form-check-label">{{ __('Notify client') }}</label>
        </div>
        <div class="form-text small">{{ __('Send reminder to client portal user') }}</div>
      </div>
      <div class="mb-3">
        <div class="form-check">
          <input type="checkbox" name="active" id="active" value="1" class="form-check-input" {{ old('active', $rule?->active ?? true) ? 'checked' : '' }}>
          <label for="active" class="form-check-label">{{ __('Active') }}</label>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    <a href="{{ route('admin.core.reminder-rules.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
  </div>
</form>
