<link href="/src/Geelhoed/geelhoed.css" type="text/css" rel="stylesheet" />

<div id="geelhoed-membermanager">
    @component('Widget/Toolbar')
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
                <th>Naam</th>
                <th>Trainingen</th>
                <th>Contactgegevens</th>
                <th>Status</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\Geelhoed\Member\Member[] $members */ @endphp
            @foreach ($members as $member)
                <tr>
                    <td>{{ $member->id }}</td>
                    @php $profile = $member->getProfile() @endphp
                    <td>
                        {{ $profile->getFullName() }}
                    </td>
                    <td>
                        @foreach ($member->getHours() as $hour)
                            {{ \Cyndaron\Util::getWeekday($hour->day) }} {{ $hour->from|hm }} - {{ $hour->until|hm }} ({{ $hour->getSportName() }}, {{ $hour->getLocation()->getName() }})
                            @if (!$loop->last) <br>@endif
                        @endforeach

                        <br><br>
                            <ul>
                                @foreach($member->getGraduationList() as $listItem)
                                    <li>{{ $listItem }})</li>
                                @endforeach
                            </ul>
                    </td>
                    <td>
                        E-mail: <a href="mailto:{{ $member->getEmail() }}">{{ $member->getEmail() }}</a>
                        @foreach ($member->getPhoneNumbers() as $phoneNumber)
                            <br>
                            Tel.nr. {{ $loop->iteration }}: {{ $phoneNumber }}
                        @endforeach
                    </td>
                    <td>
                        @if ($member->isContestant)<abbr title="Wedstrijdjudoka">W</abbr><br>@endif
                        @if ($member->canLogin())<abbr title="Kan inloggen">I</abbr><br>@endif
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
                    @include('Widget/Form/BasicInput', ['id' => 'username', 'label' => 'Gebruikersnaam', 'placeholder' => 'Bijv.: ammulder'])
                    @include('Widget/Form/BasicInput', ['id' => 'email', 'type' => 'email', 'label' => 'Eigen e-mailadres'])
                </div>
                <div class="tab-pane fade" id="personal-data" role="tabpanel" aria-labelledby="personal-data-tab">
                    @include('Widget/Form/BasicInput', ['id' => 'firstName', 'label' => 'Voornaam'])
                    @include('Widget/Form/BasicInput', ['id' => 'tussenvoegsel', 'label' => 'Tussenvoegsel'])
                    @include('Widget/Form/BasicInput', ['id' => 'lastName', 'label' => 'Achternaam'])
                    @include('Widget/Form/Select', ['id' => 'gender', 'label' => 'Geslacht', 'options' => ['male' => 'man', 'female' => 'vrouw']])

                    <h4>Contactgegevens:</h4>
                    @include('Widget/Form/BasicInput', ['id' => 'parentEmail', 'type' => 'email', 'label' => 'E-mailadres ouders'])
                    @include('Widget/Form/BasicInput', ['id' => 'phoneNumbers', 'label' => 'Telefoonnummers'])
                    @include('Widget/Form/BasicInput', ['id' => 'street', 'label' => 'Straatnaam'])
                    @include('Widget/Form/BasicInput', ['id' => 'houseNumber', 'label' => 'Huisnummer', 'type' => 'number'])
                    @include('Widget/Form/BasicInput', ['id' => 'houseNumberAddition', 'label' => 'Huisnummertoevoeging'])
                    @include('Widget/Form/BasicInput', ['id' => 'postalCode', 'label' => 'Postcode'])
                    @include('Widget/Form/BasicInput', ['id' => 'city', 'label' => 'Woonplaats'])

                    <h4>Betaalgegevens</h4>
                    @include('Widget/Form/Checkbox', ['id' => 'freeParticipation', 'label' => 'Mag gratis meedoen'])
                    @include('Widget/Form/Checkbox', ['id' => 'temporaryStop', 'label' => 'Tijdelijke stop'])
                    @include('Widget/Form/Select', ['id' => 'paymentMethod', 'label' => 'Betaalwijze', 'options' => \Cyndaron\Geelhoed\Member\Member::PAYMENT_METHODS])
                    @include('Widget/Form/BasicInput', ['id' => 'iban', 'label' => 'IBAN-nummer'])

                </div>
                <div class="tab-pane fade" id="sport" role="tabpanel" aria-labelledby="contact-tab">
                    @include('Widget/Form/Checkbox', ['id' => 'isContestant', 'label' => 'Wedstrijdjudoka'])

                    <h4>Behaalde banden</h4>
                    <ul id="gum-user-dialog-graduation-list">

                    </ul>


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
                                        @php $weekday = \Cyndaron\Util::getWeekday($hour->day) @endphp
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

<script src="/src/Geelhoed/Member/js/PageManagerTab.js" defer></script>