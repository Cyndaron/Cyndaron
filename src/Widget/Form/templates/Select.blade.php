@php $required ??= false @endphp
@php $selected ??= 0 @endphp
<div class="form-group row">
    <label for="{{ $id }}" class="col-md-3 col-form-label">{{ $label }}:</label>
    <div class="col-md-6">
        <select id="{{ $id }}" name="{{ $id }}" class="form-control custom-select" @if ($required) required @endif>
            @foreach ($options as $key => $description)
                <option value="{{ $key }}" @if ($key === $selected) selected @endif>{{ $description }}</option>
            @endforeach
        </select>
    </div>
</div>