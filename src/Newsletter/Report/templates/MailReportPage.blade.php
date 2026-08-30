@extends('Index')

@section('contents')
    <h2>Rapport</h2>

    Er zijn {{ $resultsCount }} herkende rapporten.

    @if ($resultsCount > 0)
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <td>ID</td>
                    <td>E-mailadres</td>
                    <td>Probleem</td>
                    <td>Voorgestelde actie</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                @php /** @var \Cyndaron\Newsletter\Report\Result[] $results */ @endphp
                @foreach ($results as $result)
                    <tr>
                        <td>{{ $result->messageUid }}</td>
                        <td>{{ $result->email }}</td>
                        <td>{{ $result->status->name }}</td>
                        <td>{{ $result->proposedAction->name }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form method="post" action="/newsletter/reportFixAll">
            <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('newsletter', 'reportFixAll') }}"/>
            <input type="submit" class="btn btn-primary" value="Toepassen"/>
        </form>
    @endif

@endsection
