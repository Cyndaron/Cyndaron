@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product[] $products */ @endphp
    <form method="post">
        @foreach ($products as $product)
            @include('View/Widget/Form/Checkbox', ['id' => 'product-' . $product->id, 'label' => $product->id . ' ' . $product->name, 'checked' => false])
        @endforeach

        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('webwinkel', 'uitleveren') }}"/>

        <input type="submit" value="Tonen"/>
    </form>
@endsection
