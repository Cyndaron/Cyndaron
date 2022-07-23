@php /** @var \Cyndaron\Ticketsale\Concert $concert */ @endphp
@php /** @var \Cyndaron\Ticketsale\Order\Order $order */ @endphp
<!DOCTYPE HTML>
<html lang="nl" class="cyndaron">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scannen</title>
</head>
<body>
@if ($message)
    <div style="color: {{ $isPositive ? 'green' : 'red' }}">{{ $message }}</div>
@endif

<form method="post">
    <input id="barcode" type="text" name="barcode" placeholder="barcode">
    <button type="submit">Checken</button>
</form>

</body>
