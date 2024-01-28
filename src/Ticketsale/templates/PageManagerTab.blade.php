@component('View/Widget/Toolbar')
    @slot('right')
        @include('View/Widget/Button', ['kind' => 'new', 'link' => '/editor/concert', 'title' => 'Nieuw concert', 'text' => 'Nieuw concert'])
    @endslot
@endcomponent

<table class="table table-striped table-bordered pm-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <th>Kaartsoorten</th>
            <th>Ga naar</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Ticketsale\Concert[] $concerts */ @endphp
        @foreach ($concerts as $concert)
            @php
                $concertUrl = (new \Cyndaron\Url("/concert/order/{$concert->id}"))->getFriendly();
                $encodedUrl = 'https://' . rawurlencode($_SERVER['HTTP_HOST'] . $concertUrl);
                $qrUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={$encodedUrl}&choe=UTF-8";
            @endphp
        <tr>
            <td>{{ $concert->id }}</td>
            <td>
                {{ $concert->name }}
            </td>
            <td>
                <ul>
                    @foreach (\Cyndaron\Ticketsale\TicketType::loadByConcert($concert) as $ticketType)
                        <li>
                            {{ $ticketType->name }}: {{ $ticketType->price|euro }}
{{--                            <button type="button" class="btn btn-sm btn-outline-cyndaron"><span class="glyphicon glyphicon-trash"></span></button>--}}
                        </li>
                    @endforeach
                </ul>
{{--                <button type="button" class="btn btn-sm btn-outline-cyndaron">--}}
{{--                    <span class="glyphicon glyphicon-plus"></span>--}}
{{--                    Toevoegen--}}
{{--                </button>--}}
            </td>
            <td>
                <ul>
                    <li><a href="/concert/order/{{ $concert->id }}">bestelpagina</a> (<a href="{{ $qrUrl }}" target="_blank">QR-code</a>)</li>
                    <li><a href="/concert/viewOrders/{{ $concert->id }}">overzicht bestellingen</a></li>
                    <li><a href="/concert-order/checkIn/{{ $concert->id }}/{{ $concert->secretCode }}">incheckpagina</a></li>
                </ul>
            </td>
            <td>
                <div class="btn-group">
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/concert/{{ $concert->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit concert"></span></a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="concert" data-id="{{ $concert->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('concert', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder dit concert"></span></button>
                </div>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>
