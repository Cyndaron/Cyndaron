<table id="gcam-table" class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>Beschrijving</th>
        <th>Opties</th>
        <th>Europrijs</th>
        <th>Lotenprijs</th>
    </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Webshop\Model\Product[] $products */ @endphp
        @foreach ($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->description }}</td>
                <td>
                    @php $allOptions = $product->getOptions() @endphp
                    @if (!empty($allOptions))
                        <ul>
                            @foreach ($allOptions as $key => $options)
                                @php $mappedName = $product::OPTION_MAPPING[$key] ?? $key; @endphp
                                <li>{{ $mappedName }}: {{ implode(', ', $options) }}</li>
                            @endforeach
                        </ul>
                    @endif

                </td>
                <td>{{ $product->euroPrice === null ? 'n.v.t.' : \Cyndaron\View\Template\ViewHelpers::formatEuro($product->euroPrice) }}</td>
                <td>{{ $product->gcaTicketPrice === null ? 'n.v.t.' : ($product->gcaTicketPrice . ' loten') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
