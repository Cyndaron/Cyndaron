<link href="/src/Geelhoed/geelhoed.css" type="text/css" rel="stylesheet"/>

<div id="geelhoed-membermanager">
    @component('View/Widget/Toolbar')
        @slot('left')
            <div>
                <label for="gum-filter-iban">IBAN:</label>
                <select id="gum-filter-iban" class="custom-select form-control-inline">
                    <option value="">(Alles)</option>
                    <option value="1">Met IBAN</option>
                    <option value="2">Zonder IBAN</option>
                </select>
            </div>
            <div>
                <label for="gum-filter-gender">M/V:</label>
                <select id="gum-filter-gender" class="custom-select form-control-inline">
                    <option value="">(Alles)</option>
                    <option value="male">M</option>
                    <option value="female">V</option>
                </select>
            </div>
            <div>
                <label for="gum-filter-sport">Sport:</label>
                <select id="gum-filter-sport" class="custom-select form-control-inline">
                    <option value="-1">(Alles)</option>
                    @foreach (\Cyndaron\Geelhoed\Sport\Sport::fetchAll() as $sport)
                        <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="gum-filter-graduation">Band:</label>
                <select id="gum-filter-graduation" class="custom-select form-control-inline">
                    <option value="-1">(Alles)</option>
                    @foreach (\Cyndaron\Geelhoed\Graduation::fetchAll() as $graduation)
                        <option value="{{ $graduation->id }}">{{ $graduation->getSport()->name }}
                            , {{ $graduation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="gum-filter-temporaryStop">Tijd. stop:</label>
                <select id="gum-filter-temporaryStop" class="custom-select form-control-inline">
                    <option value="-1">(Alles)</option>
                    <option value="1">Ja</option>
                    <option value="0">Nee</option>
                </select>
            </div>
            <div>
                <label for="gum-filter-paymentMethod">Bet.meth.:</label>
                <select id="gum-filter-paymentMethod" class="custom-select form-control-inline">
                    <option value="">(Alles)</option>
                    @foreach (\Cyndaron\Geelhoed\Member\Member::PAYMENT_METHODS as $paymentKey => $paymentValue)
                        <option value="{{ $paymentKey }}">{{ $paymentValue }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="gum-filter-paymentProblem">Bet.probleem:</label>
                <select id="gum-filter-paymentProblem" class="custom-select form-control-inline">
                    <option value="-1">(Alles)</option>
                    <option value="1">Ja</option>
                    <option value="0">Nee</option>
                </select>
            </div>
            <div>
                <label for="gum-filter-isContestant">Wedstrijdjudoka:</label>
                <select id="gum-filter-isContestant" class="custom-select form-control-inline">
                    <option value="-1">(Alles)</option>
                    <option value="1">Ja</option>
                    <option value="0">Nee</option>
                </select>
            </div>
            <div>
                <label for="gum-filter-location">Leslocatie:</label>
                <select id="gum-filter-location" class="custom-select form-control-inline">
                    <option value="-1">(Alles)</option>
                    @php /** @var \Cyndaron\Geelhoed\Location\Location[] $locations */ @endphp
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->getName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dob-filter">
                <label for="gum-filter-dateOfBirth-start">Geb. datum start:</label>
                <input id="gum-filter-dateOfBirth-start" class="gum-filter-dateOfBirth form-control form-control-inline"
                       type="date"/>
                <label for="gum-filter-dateOfBirth-end">eind:</label>
                <input id="gum-filter-dateOfBirth-end" class="gum-filter-dateOfBirth form-control form-control-inline"
                       type="date"/>
            </div>
            <div>
                <label>Totaal aantal leden:</label>
                <span id="gum-num-members">(onbekend)</span>
            </div>
        @endslot
        @slot('right')
            <a href="/member/directDebitList" class="btn btn-outline-cyndaron">Incassolijst</a>
            <button type="button" id="gum-new" class="btn btn-success" data-toggle="modal"
                    data-target="#gum-edit-user-dialog">
                <span class="glyphicon glyphicon-plus"></span>Nieuw lid
            </button>
        @endslot
    @endcomponent
    <table id="gum-table" class="table table-striped table-bordered pm-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Naam + adres</th>
                <th>Contactgegevens</th>
                <th>Trainingen</th>
                <th>Betaalinformatie</th>
                <th>Status</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody
            id="gum-table-body"
            data-next-quarter-start="{{ \Cyndaron\Util\Util::getStartOfNextQuarter()->format('d-m-Y') }}"
            data-csrf-token-member-delete="{{ \Cyndaron\User\User::getCSRFToken('member', 'delete') }}"
        >
        </tbody>
    </table>
</div>

<form method="post" id="gum-user-popup">
    @component('View/Widget/Modal', [
        'id' => 'gum-edit-user-dialog',
        'sizeClass' => 'modal-dialog-scrollable modal-xl',
        'title' => 'Lid bewerken'])

        @slot('body')

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="user-data-tab" data-toggle="tab" href="#user-data" role="tab"
                       aria-controls="user-data" aria-selected="true">Gebruikersgegevens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="personal-data-tab" data-toggle="tab" href="#personal-data" role="tab"
                       aria-controls="personal-data" aria-selected="false">Persoonsgegevens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sport-tab" data-toggle="tab" href="#sport" role="tab" aria-controls="sport"
                       aria-selected="false">Sportgegevens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="lessons-tab" data-toggle="tab" href="#lessons" role="tab"
                       aria-controls="lessons" aria-selected="false">Lessen</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="user-data" role="tabpanel" aria-labelledby="user-data-tab">
                    <p>De onderstaande gegevens zijn nodig voor het inloggen. Let op: het e-mailadres moet uniek zijn.
                        Is de judoka te jong om een eigen, uniek e-mailadres te hebben, laat het vak dan leeg en vul
                        alleen het e-mailadres
                        van de ouders in (onder “Persoonsgegevens”).</p>
                    <input type="hidden" name="id" value="0">
                    <input type="hidden" name="csrfToken"
                           value="{{ \Cyndaron\User\User::getCSRFToken('member', 'save') }}">
                    <input type="hidden" name="csrfTokenRemoveGraduation"
                           value="{{ \Cyndaron\User\User::getCSRFToken('member', 'removeGraduation') }}">
                    @include('View/Widget/Form/BasicInput', ['id' => 'username', 'label' => 'Gebruikersnaam', 'placeholder' => 'Bijv.: ammulder', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'email', 'type' => 'email', 'label' => 'Eigen e-mailadres', 'value' => ''])
                </div>
                <div class="tab-pane fade" id="personal-data" role="tabpanel" aria-labelledby="personal-data-tab">
                    @include('View/Widget/Form/BasicInput', ['id' => 'firstName', 'label' => 'Voornaam', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'tussenvoegsel', 'label' => 'Tussenvoegsel', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'lastName', 'label' => 'Achternaam', 'value' => ''])
                    @include('View/Widget/Form/Select', ['id' => 'gender', 'label' => 'Geslacht', 'options' => ['male' => 'man', 'female' => 'vrouw']])
                    @include('View/Widget/Form/BasicInput', ['id' => 'dateOfBirth', 'type' => 'date', 'required' => true, 'label' => 'Geboortedatum', 'value' => ''])
                    @include('View/Widget/Form/Checkbox', ['id' => 'optOut', 'label' => 'Alleen noodzakelijke communicatie (recht van verzet)'])

                    <h4>Contactgegevens:</h4>
                    @include('View/Widget/Form/BasicInput', ['id' => 'parentEmail', 'type' => 'email', 'label' => 'E-mailadres ouders', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'phoneNumbers', 'label' => 'Telefoonnummers', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'street', 'label' => 'Straatnaam', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'houseNumber', 'label' => 'Huisnummer', 'type' => 'number', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'houseNumberAddition', 'label' => 'Huisnummertoevoeging', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'postalCode', 'label' => 'Postcode', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'city', 'label' => 'Woonplaats', 'value' => ''])

                    <h4>Betaalgegevens</h4>
                    @include('View/Widget/Form/Checkbox', ['id' => 'freeParticipation', 'label' => 'Mag gratis meedoen'])
                    @include('View/Widget/Form/Number', ['id' => 'discount', 'label' => 'Korting', 'value' => 0.00, 'min' => '', 'step' => 0.01])
                    @include('View/Widget/Form/Checkbox', ['id' => 'temporaryStop', 'label' => 'Tijdelijke stop'])
                    @include('View/Widget/Form/Select', ['id' => 'paymentMethod', 'label' => 'Betaalwijze', 'options' => \Cyndaron\Geelhoed\Member\Member::PAYMENT_METHODS])
                    @include('View/Widget/Form/BasicInput', ['id' => 'iban', 'label' => 'IBAN-nummer', 'value' => ''])
                    @include('View/Widget/Form/BasicInput', ['id' => 'ibanHolder', 'label' => 'Rekeninghouder', 'value' => ''])
                    @include('View/Widget/Form/Checkbox', ['id' => 'paymentProblem', 'label' => 'Heeft betalingsprobleem'])
                    @include('View/Widget/Form/Textarea', ['id' => 'paymentProblemNote', 'label' => 'Notitie betalingsprobleem', 'value' => ''])

                </div>
                <div class="tab-pane fade" id="sport" role="tabpanel" aria-labelledby="contact-tab">
                    @include('View/Widget/Form/BasicInput', ['id' => 'joinedAt', 'type' => 'date', 'label' => 'Lid sinds'])
                    @include('View/Widget/Form/BasicInput', ['id' => 'jbnNumber', 'label' => 'JBN-nummer'])
                    @include('View/Widget/Form/Select', ['id' => 'jbnNumberLocation', 'label' => 'Locatie JBN-nummer', 'options' => ['' => 'n.v.t.', 'Walcheren' => 'Walcheren', 'Bevelanden' => 'Bevelanden']])

                    @include('View/Widget/Form/Checkbox', ['id' => 'isContestant', 'label' => 'Wedstrijdjudoka'])

                    @include('View/Widget/Form/Textarea', ['id' => 'notes', 'label' => 'Bijzonderheden', 'value' => '', 'placeholder' => 'Bijzonderheden zoals allergieën en andere zaken die voor de docent van belang kunnen zijn.'])

                    <h4>Behaalde banden</h4>
                    <ul id="gum-user-dialog-graduation-list">

                    </ul>
                    Nieuwe graduatie: <select id="new-graduation-id" name="new-graduation-id"
                                              class="form-control form-control-inline custom-select">
                        <option value=""></option>
                        @foreach (\Cyndaron\Geelhoed\Graduation::fetchAll() as $graduation)
                            <option value="{{ $graduation->id }}">{{ $graduation->getSport()->name }}
                                : {{ $graduation->name }}</option>
                        @endforeach
                    </select> <input id="new-graduation-date" name="new-graduation-date" type="date"
                                     class="form-control form-control-inline">

                </div>
                <div class="tab-pane fade" id="lessons" role="tabpanel" aria-labelledby="lessons-tab">
                    <div id="accordion">
                        @foreach (\Cyndaron\Geelhoed\Location\Location::fetchAll([], [], 'ORDER BY city') as $location)
                            @php $hours = $location->getHours(\Cyndaron\Geelhoed\Department::DEPARTMENT_ID_T_MULDER) @endphp
                            @if (empty($hours))
                                @continue
                            @endif
                            <div class="card">
                                <div class="card-header" id="heading{{ $location->id }}">
                                    <h5 class="mb-0">
                                        <button type="button" class="btn btn-link" data-toggle="collapse"
                                                data-target="#collapse{{ $location->id }}" aria-expanded="true"
                                                aria-controls="collapse{{ $location->id }}">
                                            {{ $location->city }}, {{ $location->getName() }}
                                        </button>
                                    </h5>
                                </div>

                                <div id="collapse{{ $location->id }}" class="collapse"
                                     aria-labelledby="heading{{ $location->id }}" data-parent="#accordion">
                                    <div class="card-body">
                                        @foreach ($hours as $hour)
                                            @php $weekday = \Cyndaron\View\Template\ViewHelpers::getDutchWeekday($hour->day) @endphp
                                            @include('View/Widget/Form/Checkbox', ['id' => "hour-{$hour->id}", 'label' => "{$weekday}, {$hour->getRange()} {$hour->getSportName()}"])
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @endslot
        @slot ('footer')
            <button id="gum-popup-save" type="button" class="btn btn-primary">Opslaan</button>
            <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal" aria-label="Annuleren">
                Annuleren
            </button>
        @endslot
    @endcomponent
</form>
