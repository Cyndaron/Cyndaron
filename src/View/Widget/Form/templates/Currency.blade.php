<div class="form-group row">
    <label for="{{ $id }}" class="col-sm-2 col-form-label">{{ $label }}:</label>
    <div class="input-group col-sm-3">
        <div class="input-group-prepend">
            <span class="input-group-text">€</span>
        </div>
        <input type="text" class="form-control" id="{{ $id }}" name="{{ $id }}" value="{{ $value }}">
    </div>
</div>