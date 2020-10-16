<link href="/src/Geelhoed/geelhoed.css" type="text/css" rel="stylesheet" />

<div id="geelhoed-membermanager">
    @component('Widget/Toolbar')
        @slot('left')
            IBAN:
            <select id="gum-filter-iban" class="custom-select form-control-inline">
                <option value="">(Alles)</option>
                <option value="1">Met IBAN</option>
                <option value="2">Zonder IBAN</option>
            </select>

            M/V:
            <select id="gum-filter-gender" class="custom-select form-control-inline">
                <option value="">(Alles)</option>
                <option value="male">M</option>
                <option value="female">V</option>
            </select>

            Sport:
            <select id="gum-filter-sport" class="custom-select form-control-inline">
                <option value="-1">(Alles)</option>
                @foreach (\Cyndaron\Geelhoed\Sport::fetchAll() as $sport)
                    <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                @endforeach
            </select>

            Band:
            <select id="gum-filter-graduation" class="custom-select form-control-inline">
                <option value="-1">(Alles)</option>
                @foreach (\Cyndaron\Geelhoed\Graduation::fetchAll() as $graduation)
                    <option value="{{ $graduation->id }}">{{ $graduation->getSport()->name }}, {{ $graduation->name }}</option>
                @endforeach
            </select>

            Tijd. stop:
            <select id="gum-filter-temporaryStop" class="custom-select form-control-inline">
                <option value="-1">(Alles)</option>
                <option value="1">Ja</option>
                <option value="0">Nee</option>
            </select>

            Bet.meth.
            <select id="gum-filter-paymentMethod" class="custom-select form-control-inline">
                <option value="">(Alles)</option>
                @foreach (\Cyndaron\Geelhoed\Member\Member::PAYMENT_METHODS as $paymentKey => $paymentValue)
                    <option value="{{ $paymentKey }}">{{ $paymentValue }}</option>
                @endforeach
            </select>

            Bet.probleem:
            <select id="gum-filter-paymentProblem" class="custom-select form-control-inline">
                <option value="-1">(Alles)</option>
                <option value="1">Ja</option>
                <option value="0">Nee</option>
            </select>

            Geb. datum start:
            <input id="gum-filter-dateOfBirth-start" class="gum-filter-dateOfBirth form-control form-control-inline" type="date"/>
            Geb. datum eind:
            <input id="gum-filter-dateOfBirth-end" class="gum-filter-dateOfBirth form-control form-control-inline" type="date"/>

            Wedstrijdjudoka:
            <select id="gum-filter-isContestant" class="custom-select form-control-inline">
                <option value="-1">(Alles)</option>
                <option value="1">Ja</option>
                <option value="0">Nee</option>
            </select>
        @endslot
        @slot('right')
            <button type="button" id="gum-new" class="btn btn-success" data-toggle="modal" data-target="#gum-edit-user-dialog">
                <span class="glyphicon glyphicon-plus"></span>Nieuw lid
            </button>
        @endslot
    @endcomponent
    <table class="table table-striped table-bordered pm-table">
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
        <tbody>
            @php /** @var \Cyndaron\Geelhoed\Member\Member[] $members */ @endphp
            @foreach ($members as $member)
                @php $profile = $member->getProfile() @endphp
                <tr id="pm-row-member-{{ $member->id }}"
                    class="geelhoed-member-entry"
                    data-iban="{{ $member->iban }}"
                    data-gender="{{ $profile->gender ?? '' }}"
                    data-temporaryStop="{{ (int)$member->temporaryStop }}"
                    data-paymentMethod="{{ $member->paymentMethod }}"
                    data-paymentProblem="{{ (int)$member->paymentProblem }}"
                    data-isContestant="{{ (int)$member->isContestant }}"
                    @if ($profile->dateOfBirth)
                         data-dateOfBirth="{{ date('Y-m-d', strtotime($profile->dateOfBirth)) }}"
                    @endif
                    @foreach ($member->getSports() as $sport)
                        data-sport-{{ $sport->id }}="1"
                    @endforeach
                    @foreach (\Cyndaron\Geelhoed\Sport::fetchAll() as $sport)
                        @php $graduation = $member->getHighestGraduation($sport) @endphp
                        @if ($graduation !== null)
                            data-graduation-{{ $graduation->id }}="1"
                        @endif
                    @endforeach
                >
                    <td>{{ $member->id }}</td>
                    <td>
                        {{ $profile->lastName }} {{ $profile->tussenvoegsel }} {{ $profile->firstName }}<br>
                        {{ $profile->street }} {{ $profile->houseNumber ?: '' }} {{ $profile->houseNumberAddition }}<br>
                        {{ $profile->postalCode }} {{ $profile->city }}
                    </td>
                    <td>
                        E-mail: <a href="mailto:{{ $member->getEmail() }}">{{ $member->getEmail() }}</a>
                        @foreach ($member->getPhoneNumbers() as $phoneNumber)
                            <br>
                            Tel.nr. {{ $loop->iteration }}: {{ $phoneNumber }}
                        @endforeach
                    </td>
                    <td>
                        @foreach ($member->getHours() as $hour)
                            {{ \Cyndaron\Template\ViewHelpers::getDutchWeekday($hour->day) }} {{ $hour->from|hm }} - {{ $hour->until|hm }} ({{ $hour->getSportName() }}, {{ $hour->getLocation()->getName() }})
                            @if (!$loop->last) <br>@endif
                        @endforeach
                    </td>
                    <td>
                        {{ $member->iban }}<br>
                        <abbr title="Voor kwartaal dat begint op {{ \Cyndaron\Util::getStartOfNextQuarter()->format('d-m-Y') }}">Kw.bedrag: </abbr>{{ \Cyndaron\Template\ViewHelpers::formatEuro($member->getQuarterlyFee()) }}
                    </td>
                    <td>
                        @if ($member->isContestant)<abbr title="Wedstrijdjudoka">W</abbr><br>@endif
                        @if ($member->getProfile()->canLogin())<abbr title="Kan inloggen">I</abbr><br>@endif
                        @if ($member->isSenior())<abbr title="Is senior">S</abbr>@endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-cyndaron btn-sm btn-gum-edit" data-toggle="modal" data-target="#gum-edit-user-dialog" data-id="{{ $member->id }}"><span class="glyphicon glyphicon-pencil" title="Bewerk dit lid"></span></button>
                            <button type="button" class="btn btn-danger btn-sm pm-delete" data-type="member" data-id="{{ $member->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('member', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder deze locatie"></span></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<form method="post" id="gum-user-popup">
    @component('Widget/Modal', [
        'id' => 'gum-edit-user-dialog',
        'sizeClass' => 'modal-dialog-scrollable modal-xl',
        'title' => 'Lid bewerken'])

        @slot('body')

            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="user-data-tab" data-toggle="tab" href="#user-data" role="tab" aria-controls="user-data" aria-selected="true">Gebruikersgegevens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="personal-data-tab" data-toggle="tab" href="#personal-data" role="tab" aria-controls="personal-data" aria-selected="false">Persoonsgegevens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sport-tab" data-toggle="tab" href="#sport" role="tab" aria-controls="sport" aria-selected="false">Sportgegevens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="lessons-tab" data-toggle="tab" href="#lessons" role="tab" aria-controls="lessons" aria-selected="false">Lessen</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="user-data" role="tabpanel" aria-labelledby="user-data-tab">
                    <p>De onderstaande gegevens zijn nodig voor het inloggen. Let op: het e-mailadres moet uniek zijn.
                    Is de judoka te jong om een eigen, uniek e-mailadres te hebben, laat het vak dan leeg en vul alleen het e-mailadres
                    van de ouders in (onder “Persoonsgegevens”).</p>
                    <input type="hidden" name="id" value="0">
                    <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('member', 'save') }}">
                    <input type="hidden" name="csrfTokenRemoveGraduation" value="{{ \Cyndaron\User\User::getCSRFToken('member', 'removeGraduation') }}">
                    @include('Widget/Form/BasicInput', ['id' => 'username', 'label' => 'Gebruikersnaam', 'placeholder' => 'Bijv.: ammulder', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'email', 'type' => 'email', 'label' => 'Eigen e-mailadres', 'value' => ''])
                </div>
                <div class="tab-pane fade" id="personal-data" role="tabpanel" aria-labelledby="personal-data-tab">
                    @include('Widget/Form/BasicInput', ['id' => 'firstName', 'label' => 'Voornaam', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'tussenvoegsel', 'label' => 'Tussenvoegsel', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'lastName', 'label' => 'Achternaam', 'value' => ''])
                    @include('Widget/Form/Select', ['id' => 'gender', 'label' => 'Geslacht', 'options' => ['male' => 'man', 'female' => 'vrouw']])
                    @include('Widget/Form/BasicInput', ['id' => 'dateOfBirth', 'type' => 'date', 'required' => true, 'label' => 'Geboortedatum', 'value' => ''])

                    <h4>Contactgegevens:</h4>
                    @include('Widget/Form/BasicInput', ['id' => 'parentEmail', 'type' => 'email', 'label' => 'E-mailadres ouders', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'phoneNumbers', 'label' => 'Telefoonnummers', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'street', 'label' => 'Straatnaam', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'houseNumber', 'label' => 'Huisnummer', 'type' => 'number', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'houseNumberAddition', 'label' => 'Huisnummertoevoeging', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'postalCode', 'label' => 'Postcode', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'city', 'label' => 'Woonplaats', 'value' => ''])

                    <h4>Betaalgegevens</h4>
                    @include('Widget/Form/Checkbox', ['id' => 'freeParticipation', 'label' => 'Mag gratis meedoen'])
                    @include('Widget/Form/Checkbox', ['id' => 'temporaryStop', 'label' => 'Tijdelijke stop'])
                    @include('Widget/Form/Select', ['id' => 'paymentMethod', 'label' => 'Betaalwijze', 'options' => \Cyndaron\Geelhoed\Member\Member::PAYMENT_METHODS])
                    @include('Widget/Form/BasicInput', ['id' => 'iban', 'label' => 'IBAN-nummer', 'value' => ''])
                    @include('Widget/Form/BasicInput', ['id' => 'ibanHolder', 'label' => 'Rekeninghouder', 'value' => ''])
                    @include('Widget/Form/Checkbox', ['id' => 'paymentProblem', 'label' => 'Heeft betalingsprobleem'])
                    @include('Widget/Form/Textarea', ['id' => 'paymentProblemNote', 'label' => 'Notitie betalingsprobleem', 'value' => ''])

                </div>
                <div class="tab-pane fade" id="sport" role="tabpanel" aria-labelledby="contact-tab">
                    @include('Widget/Form/BasicInput', ['id' => 'joinedAt', 'type' => 'date', 'label' => 'Lid sinds'])
                    @include('Widget/Form/BasicInput', ['id' => 'jbnNumber', 'label' => 'JBN-nummer'])
                    @include('Widget/Form/Select', ['id' => 'jbnNumberLocation', 'label' => 'Locatie JBN-nummer', 'options' => ['' => 'n.v.t.', 'Walcheren' => 'Walcheren', 'Bevelanden' => 'Bevelanden']])

                    @include('Widget/Form/Checkbox', ['id' => 'isContestant', 'label' => 'Wedstrijdjudoka'])

                    @include('Widget/Form/Textarea', ['id' => 'notes', 'label' => 'Bijzonderheden', 'value' => '', 'placeholder' => 'Bijzonderheden zoals allergieën en andere zaken die voor de docent van belang kunnen zijn.'])

                    <h4>Behaalde banden</h4>
                    <ul id="gum-user-dialog-graduation-list">

                    </ul>
                    Nieuwe graduatie: <select id="new-graduation-id" name="new-graduation-id" class="form-control form-control-inline custom-select">
                        <option value=""></option>
                        @foreach (\Cyndaron\Geelhoed\Graduation::fetchAll() as $graduation)
                            <option value="{{ $graduation->id }}">{{ $graduation->getSport()->name }}: {{ $graduation->name }}</option>
                        @endforeach
                    </select> <input id="new-graduation-date" name="new-graduation-date" type="date" class="form-control form-control-inline">

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
                                    <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapse{{ $location->id }}" aria-expanded="true" aria-controls="collapse{{ $location->id }}">
                                        {{ $location->getName() }}
                                    </button>
                                </h5>
                            </div>

                            <div id="collapse{{ $location->id }}" class="collapse" aria-labelledby="heading{{ $location->id }}" data-parent="#accordion">
                                <div class="card-body">
                                    @foreach ($hours as $hour)
                                        @php $weekday = \Cyndaron\Template\ViewHelpers::getDutchWeekday($hour->day) @endphp
                                        @include('Widget/Form/Checkbox', ['id' => "hour-{$hour->id}", 'label' => "{$weekday}, {$hour->getRange()} {$hour->getSportName()}"])
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
            <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal" aria-label="Annuleren">Annuleren</button>
        @endslot
    @endcomponent
</form>
