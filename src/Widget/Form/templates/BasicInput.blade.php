<div class="form-group row">
    <label for="{{ $id }}" class="col-md-3 col-form-label">{{ $label }}:</label>
    <div class="col-md-6">
        <input
            type="{{ $inputType ?? $type ?? 'text' }}"
            class="form-control"
            id="{{ $id }}"
            name="{{ $id }}"
            value="{{ $value ?? '' }}"
            @if (isset($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if (isset($min)) min="{{ $min }}" @endif
            @if (isset($max)) max="{{ $max }}" @endif
            @if (isset($step)) step="{{ $step }}" @endif
            @if (isset($pattern)) pattern="{{ $pattern }}" @endif
            @if (!empty($required)) required @endif>
    </div>
</div>