@extends ('Editor/PageBase')

@section ('contentSpecificButtons')
    <input type="hidden" name="hash" value="{{ $hash }}"/>
    <input type="hidden" name="photoalbumId" value="{{ $photoalbumId }}"/>
@endsection
