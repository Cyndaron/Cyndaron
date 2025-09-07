<tr>
    <td>
        {{ $name }}
        @if (!empty(($description)))
            <br><small>{{ $description }}</small>
        @endif
    </td>
    <td>{{ $price|euro }}</td>
    <td>
        <input class="numTickets form-control form-control-inline" readonly="readonly" size="2" name="tickettype-{{ $id }}" id="tickettype-{{ $id }}" value="0"/>
        <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-increase" data-kaartsoort="{{ $id }}">@include('View/Widget/Icon', ['type' => 'new'])</button>
        <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-decrease" data-kaartsoort="{{ $id }}">@include('View/Widget/Icon', ['type' => 'minus'])</button>
    </td>
</tr>
