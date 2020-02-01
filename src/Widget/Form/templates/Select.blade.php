@php $required ??= false @endphp
<div class="form-group row">
    <label for="{{ $id }}" class="col-md-3 col-form-label">{{ $label }}:</label>
    <div class="col-md-6">
        <select id="{{ $id }}" name="{{ $id }}" class="form-control custom-select" @if ($required) required @endif>
            @foreach ($options as $key => $description)
                <option value="{{ $key }}">{{ $description }}</option>
            @endforeach
        </select>
    </div>
</div>