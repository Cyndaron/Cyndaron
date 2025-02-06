<table class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>URI</th>
        <th>Cache-timeout</th>
        <th>In cache</th>
        <th>Acties</th>
    </tr>
    </thead>
    <tbody>
    @php
        /** @var \Cyndaron\Geelhoed\Tryout\Tryout[] $tryouts */
        $i = 0;
    @endphp
    @foreach (\Cyndaron\OpenRCT2\Downloads\APICall::cases() as $apiCall)
        <tr>
            <td>{{ $i++ }}</td>
            <td>{{ $apiCall->name }}</td>
            <td>{{ $apiCall->value }}</td>
            <td>{{ $apiCall->getAPITimeout() / 60 }} minuten</td>
            <td>{{ $apiCall->presentInCache()|boolToDingbat }}</td>
            <td>
                @if ($apiCall->presentInCache())
                    <form method="post" action="/download/clearCache/{{ $apiCall->name }}">
                        <button type="submit" class="btn btn-outline-cyndaron" title="Cache verwijderen">
                            <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('download', 'clearCache') }}">
                            @include('View/Widget/Icon', ['type' => 'delete'])
                        </button>
                    </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
