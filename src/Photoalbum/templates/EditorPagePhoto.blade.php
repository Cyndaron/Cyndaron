@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    <input type="hidden" name="hash" value="{{ $hash }}"/>
@endsection