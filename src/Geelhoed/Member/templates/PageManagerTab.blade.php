<link href="/src/Geelhoed/geelhoed.css" type="text/css" rel="stylesheet" />

<div id="geelhoed-membermanager">
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
                    </td>
                    <td>
                        E-mail: {{ $member->email }}
                        @foreach ($member->getPhoneNumbers() as $phoneNumber)
                            <br>
                            Tel.nr. {{ $loop->iteration }}: {{ $phoneNumber }}
                        @endforeach
                    </td>
                    <td>
                        @if ($member->isContestant)<abbr title="Wedstrijdjudoka">W</abbr><br>@endif
                        @if ($member->userId)<abbr title="Kan inloggen">I</abbr><br>@endif
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-cyndaron btn-sm" data-toggle="modal" data-target="#gum-edit-user-dialog"><span class="glyphicon glyphicon-pencil" title="Bewerk dit lid"></span></button>
                            <button type="button" class="btn btn-danger btn-sm pm-delete" data-type="member" data-id="{{ $member->id }}" data-csrf-token="{{ \Cyndaron\User\User::getCSRFToken('member', 'delete') }}"><span class="glyphicon glyphicon-trash" title="Verwijder deze locatie"></span></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@component('Widget/Modal', [
    'id' => 'gum-edit-user-dialog',
    'sizeClass' => 'modal-dialog-scrollable modal-xl',
    'title' => 'Lid bewerken'])

    @slot('body')
        @include('Editor/InputText', ['id' => 'firstName', 'label' => 'Voornaam'])
        @include('Editor/InputText', ['id' => 'tussenvoegsel', 'label' => 'Tussenvoegsel'])
        @include('Editor/InputText', ['id' => 'lastName', 'label' => 'Achternaam'])
        @include('Editor/Checkbox', ['id' => 'isContestant', 'label' => 'Wedstrijdjudoka'])
    @endslot
    @slot ('footer')
        aaaaaa
    @endslot
@endcomponent
