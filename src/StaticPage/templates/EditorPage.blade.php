@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    @include ('Editor/Checkbox', ['id' => 'enableComments', 'description' => 'Reacties toestaan', 'checked' => $enableComments])

    <div class="form-group row">
        <label for="tags" class="col-sm-2 col-form-label">Tags</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="tags" name="tags" value="{{ $tags }}">
        </div>
    </div>
@endsection