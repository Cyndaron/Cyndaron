@php
/** @var \Cyndaron\OpenRCT2\Multiplayer\Server[] $servers */
@endphp
@extends('Index')

@section('contents')
    <table class="table table-striped table-bordered serverlist">
        <thead>
        <tr>
            <th>{{ $t->get('openrct2.multiplayer.serverlist.server') }}</th>
            <th>{{ $t->get('openrct2.multiplayer.serverlist.date') }}</th>
            <th>{{ $t->get('openrct2.multiplayer.serverlist.players') }}</th>
            <th>🔒</th>
            <th>{{ $t->get('openrct2.multiplayer.serverlist.version') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($servers as $server)
            <tr>
                <td>
                    {!! $server->name !!}
                    @if ($server->description)
                        <div class="description">{!! $server->description !!}</div>
                    @endif
                </td>
                <td>{{ $server->dateFormatted }}</td>
                <td>{{ $server->playersCurrently }} / {{ $server->playersMaximum }}</td>
                <td>@if ($server->requiresPassword)🔒@endif</td>
                <td>{{ $server->version }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
