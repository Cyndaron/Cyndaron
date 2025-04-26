@extends('Index')

@section('contents')
    @php /** @var \Cyndaron\Minecraft\Server\Server[] $servers */ @endphp
    @foreach ($servers as $server)
        <br>
        <h3>{{ $server->name }} ({{ $server->isOnline ? 'online' : 'offline'}})</h3>
        @if ($server->isOnline)
            Aantal spelers online: {{ $server->onlinePlayers }} (maximaal {{ $server->maxPlayers }})<br/>
            Versie: {{ $server->gameVersion }}<br/>
            <abbr title="Message of the day">MOTD</abbr>: {!! $server->motd !!}<br/>
        @endif
    @endforeach

    @foreach ($servers as $server)
        @if ($server->isOnline && $server->dynmapPort > 0)
            <br>
            <h3>Landkaart {{ $server->name }} <a href="http://{{ $server->hostname }}:{{ $server->dynmapPort }}"
                                                 class="btn btn-outline-cyndaron"
                                                 role="button">@include('View/Widget/Icon', ['type' => 'resize'])
                    Maximaliseren</a></h3><br>
            <iframe class="dynmap-embed" src="/minecraft/dynmapproxy/{{ $server->id }}/" width="800"
                    height="600"></iframe>
            <br>
        @endif
    @endforeach
@endsection
