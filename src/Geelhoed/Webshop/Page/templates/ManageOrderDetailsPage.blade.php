@extends('Index')

@section('contents')
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Artikel</th>
                <th>Configuratie</th>
                <th>Aantal</th>
                <th>Bedrag</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Geelhoed\Webshop\Model\OrderItem[] $orderItems */ @endphp
            @foreach ($orderItems as $orderItem)
                <tr>
                    <td>{{ $orderItem->id }}</td>
                    <td>{{ $orderItem->product->name }}</td>
                    <td>
                        <ul>
                            @foreach ($orderItem->getOptions() as $key => $value)
                                <li>
                                    {{ \Cyndaron\Geelhoed\Webshop\Model\Product::OPTION_MAPPING[$key] ?? $key }}:
                                    {{ $value }}
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td>{{ $orderItem->quantity }}</td>
                    <td>
                        @if ($orderItem->currency === \Cyndaron\Geelhoed\Webshop\Model\Currency::EURO)
                            {{ $orderItem->price|euro }}
                        @else
                            {{ $orderItem->price }}&nbsp;loten
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
