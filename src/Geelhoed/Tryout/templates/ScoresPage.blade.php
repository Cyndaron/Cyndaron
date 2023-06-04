@extends('Index')

@section('contents')
    @if (count($rows) > 0)
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Aantal punten</th>
                <th>Lopend totaal</th>
            </tr>
        </thead>
        <tbody>
        @php /** @var \Cyndaron\Geelhoed\Tryout\PointsRow[] $rows */ @endphp
        @foreach ($rows as $row)
            <tr>
                <td>@if ($row->date){{ $row->date|dmy }}@else Onbekend @endif</td>
                <td>{{ $row->points }}</td>
                <td>{{ $row->accumulativeTotal }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
        @include('View/Widget/ErrorMessage', ['text' => 'Voor dit nummer zijn geen punten gevonden. Controleer of je het juiste nummer hebt ingevoerd.'])

        @include('Geelhoed/Tryout/ScoresForm')
    @endif
@endsection
