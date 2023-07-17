@extends('Index')

@section('contents')
    <p>
        U kunt hier de scores van het Tryout-toernooi opvragen door het viercijferige nummer op de deelnemerskaart in te vullen.
        Om privacyredenen tonen wij niet welke namen aan welke nummers gekoppeld zijn.
    </p>
    <p>
        Komt het aantal punten niet overeen met het kaartje,
        dan kunt u dit op het volgende toernooi laten rechtzetten bij de hoofdjury.
    </p>

    @include('Geelhoed/Tryout/ScoresForm')
@endsection
