@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Minecraft\Server[] $servers */ @endphp
    @foreach ($servers as $server)
        <br>
        <h3>{{ $server->name }} ({{ $server->isOnline ? 'online' : 'offline'}})</h3>
        @if ($server->isOnline)
            Aantal spelers online: {{ $server->onlinePlayers }} (maximaal {{ $server->maxPlayers }})<br />
            Versie: {{ $server->gameVersion }}<br />
            <abbr title="Message of the day">MOTD</abbr>: {!! $server->motd !!}<br />
        @endif
    @endforeach

    @foreach ($servers as $server)
        @if ($server->isOnline)
            <br>
            <h3>Landkaart {{ $server->name }} <a href="http://{{ $server->hostname }}:{{ $server->dynmapPort }}" class="btn btn-outline-cyndaron" role="button"><span class="glyphicon glyphicon-resize-full"></span> Maximaliseren</a></h3><br>
            <iframe src="/minecraft/dynmapproxy/{{ $server->id }}/" style="border-radius:7px;" width="800" height="600"></iframe>
            <br>
        @endif
    @endforeach
@endsection