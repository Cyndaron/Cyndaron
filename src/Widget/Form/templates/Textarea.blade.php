<div class="form-group row">
    <label for="{{ $id }}" class="col-sm-2 col-form-label">{{ $label }}:</label>
    <div class="col-sm-5">
        <textarea class="form-control" id="{{ $id }}" name="{{ $id }}" rows="4">{{ $value ?? '' }}</textarea>
    </div>
</div>