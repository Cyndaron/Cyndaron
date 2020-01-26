<div class="form-group row">
    <label for="{{ $id }}" class="col-lg-2 col-form-label">{{ $label }}:</label>
    <div class="col-lg-5">
        <input
            type="{{ $inputType ?? $type ?? 'text' }}"
            class="form-control"
            id="{{ $id }}"
            name="{{ $id }}"
            value="{{ $value ?? '' }}"
            @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if (!empty($required)) required @endif>
    </div>
</div>