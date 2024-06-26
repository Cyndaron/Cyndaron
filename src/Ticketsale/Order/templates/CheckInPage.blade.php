@php /** @var \Cyndaron\Ticketsale\Concert\Concert $concert */ @endphp
@php /** @var \Cyndaron\Ticketsale\Order\Order $order */ @endphp
<!DOCTYPE HTML>
<html lang="nl" class="cyndaron">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scannen {{ $concert->name }}</title>
    <link href="/src/Ticketsale/Order/css/CheckIn.css" type="text/css" rel="stylesheet" />
</head>
<body>
<h1>Scannen {{ $concert->name }}</h1>
@if ($message)
    <div class="message {{ $isPositive ? 'message-positive' : 'message-negative' }}">
        {{ $isPositive ? '✅' : '❌' }}
        {{ $message }}
        <div class="close"><a href="/concert-order/checkIn/{{ $concert->id }}/{{ $concert->secretCode }}">×</a></div>
    </div>
@endif

<form method="post">
    <button type="button" id="start-scan">Start scannen</button>
    <input id="barcode" type="text" name="barcode" value="barcode" autocomplete="off">
    <button type="submit">Checken</button>
</form>

<script src="/src/Ticketsale/Order/js/CheckInPage.js" nonce="{{ $nonce }}"></script>
</body>
</html>
