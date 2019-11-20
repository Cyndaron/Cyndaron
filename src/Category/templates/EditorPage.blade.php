@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    <div class="form-group row">
        <label class="col-sm-2 col-form-label" for="viewMode">{{ $label }}: </label>
        <div class="col-sm-5">
            <select id="viewMode" name="viewMode" class="form-control custom-select">
                @foreach ($options as $value => $description)
                    <option value="{{ $value }}" @if ($value == $selected) selected @endif>{{ $description }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endsection