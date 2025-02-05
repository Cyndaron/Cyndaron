@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product[] $products */ @endphp
    @php /** @var array<string, int> $totalsPerProduct */ @endphp
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Product</th>
            <th>Aantal</th>
        </tr>
        </thead>
        <tbody>
        @foreach($totalsPerProduct as $description => $count)
            <tr>
                <td>{{ $description }}</td><td>{{ $count }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>


@endsection
