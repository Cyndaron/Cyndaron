<div class="form-group form-check">
    <input type="checkbox" class="form-check-input" id="{{ $id }}" name="{{ $id }}" @if ($checked ?? false) checked @endif value="1">
    <label class="form-check-label" for="{{ $id }}">{{ $description ?? $label }}</label>
</div>