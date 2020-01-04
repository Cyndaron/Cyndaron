@extends('Index')

@section('contents')
    @if ($isAdmin)
        <form method="post" action="/filecabinet/addItem" enctype="multipart/form-data">
            <label for="newFile">Bestand toevoegen:</label>
            <input type="file" id="newFile" name="newFile" required>
            <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('filecabinet', 'addItem') }}">
            <input class="btn btn-primary" type="submit" value="Uploaden">
        </form>
        <hr>
    @endif

    @if ($introduction)
        {!! $introduction !!}
        <hr>
    @endif

    <ul>
        @foreach ($files as $filename)
            <li>
                <a href="/bestandenkast/{{ $filename }}">{{ pathinfo($filename, PATHINFO_FILENAME) }}</a>
                @if ($isAdmin)
                    <form method="post" action="/filecabinet/deleteItem" style="display: inline">
                        <input type="hidden" name="csrfToken" value="{{ $deleteCsrfToken }}">
                        <input type="hidden" name="filename" value="{{ $filename }}">
                        <button class="btn btn-sm btn-danger" type="submit" title="Dit bestand verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
@endsection