@php /** @var \Cyndaron\Url\UrlService $urlService */ @endphp
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
        @php /** @var \Cyndaron\Ticketsale\Concert\Concert[] $concerts */ @endphp
        @foreach ($concerts as $concert)
            @php
                $concertUrl = $urlService->toFriendly(new \Cyndaron\Url\Url("/concert/order/{$concert->id}"));
                $fullUrl = $baseUrl . $concertUrl;
                $qrUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={$fullUrl}&choe=UTF-8";
            @endphp
        <tr>
            <td>{{ $concert->id }}</td>
            <td>
                {{ $concert->name }}
            </td>
            <td>
                <ul>
                    @foreach (\Cyndaron\Ticketsale\TicketType\TicketType::loadByConcert($concert) as $ticketType)
                        <li>
                            {{ $ticketType->name }}: {{ $ticketType->price|euro }}
                            <a href="/editor/ticketType/{{ $ticketType->id }}" role="button" class="btn btn-sm btn-outline-cyndaron">
                                @include('View/Widget/Icon', ['type' => 'edit'])
                            </a>
{{--                            <button type="button" class="btn btn-sm btn-outline-cyndaron">@include('View/Widget/Icon', ['type' => 'delete'])</button>--}}
                        </li>
                    @endforeach
                </ul>
                <a href="/editor/ticketType/0/{{ $concert->id }}" role="button" class="btn btn-sm btn-outline-cyndaron">
                    @include('View/Widget/Icon', ['type' => 'new'])
                    Toevoegen
                </a>
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
                    <a class="btn btn-outline-cyndaron btn-sm" href="/editor/concert/{{ $concert->id }}" title="Bewerk dit concert">@include('View/Widget/Icon', ['type' => 'edit'])</a>
                    <button class="btn btn-danger btn-sm pm-delete" data-type="concert" data-id="{{ $concert->id }}" data-csrf-token="{{ $tokenDelete }}" title="Verwijder dit concert">@include('View/Widget/Icon', ['type' => 'delete'])</button>
                </div>

            </td>
        </tr>
        @endforeach
    </tbody>
</table>
