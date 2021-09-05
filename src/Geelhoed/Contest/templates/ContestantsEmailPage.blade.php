@extends ('Index')

@section ('contents')
    Lijst van e-mailadressen van de ouders van wedstrijdjudoka’s, plus eigen adressen voor volwassen judoka’s.

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Naam</th>
                <th>E-mailadres</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emailAddressPairs as $emailAddressPair)
                <tr>
                    <td>{!! $emailAddressPair['name'] !!}</td>
                    <td>{!! $emailAddressPair['email'] !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Copy-paste-versie</h2>
    <pre>
@foreach ($emailAddressPairs as $emailAddressPair)
{!! $emailAddressPair['name'] !!} &lt;{!! $emailAddressPair['email'] !!}&gt;
@endforeach
    </pre>
@endsection
