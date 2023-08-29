@extends('Index')

@section('contents')
    <h2>Abonnees</h2>
    <p>Er zijn {{ count($subscribers) }} nieuwsbriefabonnees.</p>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Naam</th>
                <th>E-mailadres</th>
                <th>Bevestigd</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Newsletter\Subscriber[] $subscribers */ @endphp
            @foreach ($subscribers as $subscriber)
                <tr>
                    <td>{{ $subscriber->name }}</td>
                    <td><a href="mailto:{{ $subscriber->email }}">{{ $subscriber->email }}</a></td>
                    <td>{{ $subscriber->confirmed|boolToDingbat }}</td>
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

    <h3 id="unsubscribe">Uitschrijven</h3>

    <form method="post" action="/newsletter/unsubscribe">
        @include('View/Widget/Form/Email', ['id' => 'email', 'label' => 'E-mailadres', 'value' => ''])
        <input type="hidden" name="csrfToken" value="{{ $csrfTokenUnsubscribe }}"/>
        <input type="submit" class="btn btn-primary" value="Uitschrijven"/>
    </form>

    <h3 id="delete">Foutieve adressen verwijderen</h3>

    <form method="post" action="/newsletter/delete">
        @include('View/Widget/Form/Email', ['id' => 'email', 'label' => 'E-mailadres', 'value' => ''])
        <input type="hidden" name="csrfToken" value="{{ $csrfTokenDelete }}"/>
        <input type="submit" class="btn btn-primary" value="Verwijderen"/>
    </form>

@endsection
