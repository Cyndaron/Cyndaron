@php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product $product */ @endphp

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $product->name }}</h5>
        @if ($product->description)
            <p class="card-text">{{ $product->description }}</p>
        @endif

        @foreach ($product->getOptions() as $key => $options)
            @php $mappedName = $product::OPTION_MAPPING[$key] ?? $key; @endphp
            <p>{{ $mappedName }}:
                @foreach ($options as $option)
                    <input
                        type="radio"
                        class="{{ $product->id }}-{{ $key}}"
                        id="{{ $product->id }}-{{ $key}}-{{ $option }}"
                        name="{{ $product->id }}-{{ $key}}" value="{{ $option }}">
                    <label for="{{ $product->id }}-{{ $key}}-{{ $option }}">{{ $option }}</label>
                @endforeach
            </p>
        @endforeach

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
                    In winkelmand ({{ $product->getGcaTicketPrice() }} loten)
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
                    In winkelmand ({{ $product->getEuroPrice()|euro }})
                </button>
            @endif
        @endforeach
    </div>

</div>
