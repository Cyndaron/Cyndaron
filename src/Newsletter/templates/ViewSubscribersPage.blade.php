@extends('Index')

@section('contents')
    <h2>Abonnees</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Naam</th>
                <th>E-mailadres</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Newsletter\Subscriber[] $subscribers */ @endphp
            @foreach ($subscribers as $subscriber)
                <tr>
                    <td>{{ $subscriber->name }}</td>
                    <td><a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Copy-paste-versie</h3>
    <p>Deze lijst kan gekopieerd worden en geplakt in een e-mailclient.</p>

    <pre>
@foreach ($subscribers as $subscriber)
{{ $subscriber->name }} &lt;{{ $subscriber->email }}&gt;
@endforeach
    </pre>

@endsection