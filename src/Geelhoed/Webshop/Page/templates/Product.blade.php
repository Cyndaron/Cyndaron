@php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product $product */ @endphp

<div class="card">

    <a class="card-img-top" href="/uploads/images/webshop/{{ $product->id }}/image.jpg">
        <img class="" src="/uploads/images/webshop/{{ $product->id }}/thumbnail.jpg" alt="Afbeelding {{ $product->name }}">
    </a>
    <div class="card-body">
        <h5 class="card-title">{{ $product->name }}</h5>
        <p class="card-text">
            {!! $product->description !!}
        </p>

        @foreach ($product->getOptions() as $key => $options)
            @php $mappedName = $product::OPTION_MAPPING[$key] ?? $key; @endphp
            <h6>{{ $mappedName }}:</h6>
            <p>
                @foreach ($options as $option)
                    <span class="form-check form-check-inline">
                        <input
                            type="radio"
                            class="form-check-input {{ $product->id }}-{{ $key}}"
                            id="{{ $product->id }}-{{ $key}}-{{ $option }}"
                            name="{{ $product->id }}-{{ $key}}" value="{{ $option }}">
                        <label
                            class="form-check-label"
                            for="{{ $product->id }}-{{ $key}}-{{ $option }}">
                            {{ $option }}
                        </label>
                    </span>
                @endforeach
            </p>
          @endforeach


        <div class="addtocart-block">
            <h6>In winkelmand:</h6>

            <div class="product-currencies">
                @foreach ($validCurrencies as $validCurrency)
                    @if ($validCurrency === \Cyndaron\Geelhoed\Webshop\Model\Currency::LOTTERY_TICKET)
                        <button
                            type="button"
                            class="btn btn-primary addToCart"
                            data-hash="{{ $hash }}"
                            data-product-id="{{ $product->id }}"
                            data-product-options="{{ implode(',', array_keys($product->getOptions())) }}"
                            data-currency="LOT"
                        >
                            {{ $product->getGcaTicketPrice() }} loten
                        </button>
                    @elseif($validCurrency === \Cyndaron\Geelhoed\Webshop\Model\Currency::EURO)
                        <button
                            type="button"
                            class="btn btn-primary addToCart"
                            data-hash="{{ $hash }}"
                            data-product-id="{{ $product->id }}"
                            data-product-options="{{ implode(',', array_keys($product->getOptions())) }}"
                            data-currency="EUR"
                        >
                            {{ $product->getEuroPrice()|euro }}
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

</div>
