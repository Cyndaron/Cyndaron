@extends ('Index')

@section ('contents')
    <p>Wilt u liever alles in één keer zien? <a href="/locaties/overzicht">Klik dan hier.</a></p>

    <div id="location-search-accordion">
        <div class="card">
            <div class="card-header" id="heading-cities">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#cities-list" aria-expanded="false" aria-controls="cities-list">
                        Zoeken op plaats
                    </button>
                </h5>
            </div>
            <div id="cities-list" class="collapse" aria-labelledby="heading-cities" data-parent="#location-search-accordion">
                <div class="card-body">
                    <div id="location-search-cities" class="location-search-block">
                        @foreach ($cities as $city)
                            <div class="col-md-4">
                                <a href="/locaties/in-stad/{{ $city|slug }}">
                                    <div class="card">
                                        <div class="card-body">
                                            {{ $city }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading-days">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#days-list" aria-expanded="false" aria-controls="days-list">
                        Zoeken op dag
                    </button>
                </h5>
            </div>
            <div id="days-list" class="collapse" aria-labelledby="heading-days" data-parent="#location-search-accordion">
                <div class="card-body">
                    <div id="location-search-days" class="location-search-block">
                        @foreach ($days as $number => $readable)
                            <div class="col-md-4">
                                <a href="/locaties/op-dag/{{ $number }}">
                                    <div class="card">
                                        <div class="card-body">
                                            {{ $readable }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading-age">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed" data-bs-toggle="collapse" data-bs-target="#age-list" aria-expanded="false" aria-controls="age-list">
                        Zoeken op leeftijd
                    </button>
                </h5>
            </div>
            <div id="age-list" class="collapse" aria-labelledby="heading-age" data-parent="#location-search-accordion">
                <div class="card-body">
                    @component('View/Widget/Form/FormWrapper', ['id' => 'search-by-age-age', 'label' => 'Leeftijd'])
                        @slot('right')
                            <div class="input-group">
                                <input id="search-by-age-age" type="number" min="4" step="1" pattern="[0-9]+" maxlength="3" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text">jaar</span>
                                </div>
                            </div>
                        @endslot
                    @endcomponent
                    @component('View/Widget/Form/FormWrapper', ['id' => 'search-by-age-sport', 'label' => 'Sport'])
                        @slot('right')
                            @foreach ($sports as $sport)
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="search-by-age-sport-{{ $sport->id }}" name="search-by-age-sport" class="custom-control-input" value="{{ $sport->id }}">
                                    {{--                    <input id="search-by-age-sport-{{ $sport->id }}" name="search-by-age-sport" type="radio" value="{{ $sport->id }}" class="form-control"> --}}
                                    <label class="custom-control-label" for="search-by-age-sport-{{ $sport->id }}">{{ $sport->name }}</label>
                                </div>

                            @endforeach

                        @endslot
                    @endcomponent

                    <button id="search-by-age-submit" class="btn btn-primary">Zoeken</button>
                </div>
            </div>
        </div>
    </div>



@endsection
