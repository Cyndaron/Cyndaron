@extends ('Index')

@section ('contents')
    <h2>Zoeken op plaats</h2>
    Sportschool Geelhoed is actief op de volgende plaatsen:

    <ul id="location-search-cities">
        @foreach ($cities as $city)
            <li><a href="/location/overview#{{ $city }}">{{ $city }}</a></li>
        @endforeach
    </ul>

    <h2>Zoeken op leeftijd</h2>
    Ik ben <input id="search-by-age-age" type="number" min="4" step="1" pattern="[0-9]+" maxlength="3"> jaar.

    <button id="search-by-age-submit" class="btn btn-primary">Zoeken</button>

@endsection
