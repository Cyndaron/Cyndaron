<div class="form-group row">
    <label for="{{ $id }}" class="col-lg-2 col-form-label">{{ $label }}:</label>
    <div class="col-lg-5">
        <select id="{{ $id }}" name="{{ $id }}" class="form-control custom-select">
            @foreach ($options as $key => $description)
                <option value="{{ $key }}">{{ $description }}</option>
            @endforeach
        </select>
    </div>
</div>