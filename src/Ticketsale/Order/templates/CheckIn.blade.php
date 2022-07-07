@php /** @var \Cyndaron\Ticketsale\Concert $concert */ @endphp
@php /** @var \Cyndaron\Ticketsale\Order\Order $order */ @endphp

@if ($message)
    <div style="color: {{ $isPositive ? 'green' : 'red' }}">{{ $message }}</div>
@endif

<form method="post">
    <input id="barbode" type="text" name="barcode" placeholder="barcode">
    <button type="submit">Checken</button>
</form>
