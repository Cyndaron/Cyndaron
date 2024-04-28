@extends ('Index')

@section ('contents')
    @component('View/Widget/Toolbar2')
        @slot('right')
            <button id="gpam-new" class="btn btn-success" data-toggle="modal" data-target="#gpm-edit-dialog">Toevoegen</button>
        @endslot
    @endcomponent
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Ouder(s)</th>
                <th>E-mailadres</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\User\User[] $users */ @endphp
            @php $deleteToken = \Cyndaron\User\UserSession::getCSRFToken('contest', 'deleteParentAccount') @endphp
            @php $deleteFromToken = \Cyndaron\User\UserSession::getCSRFToken('contest', 'deleteFromParentAccount') @endphp
            @php $addToToken = \Cyndaron\User\UserSession::getCSRFToken('contest', 'addToParentAccount') @endphp
            @php $resetPasswordToken = \Cyndaron\User\UserSession::getCSRFToken('user', 'resetpassword') @endphp
            @php /** @var array<int, \Cyndaron\Geelhoed\Member\Member> $contestants */ @endphp
            @php /** @var array<int, \Cyndaron\User\User[]> $parentsPerContestant */ @endphp
            @foreach ($contestants as $contestant)
                @php $parentsForThis = $parentsPerContestant[$contestant->id] ?? []; @endphp
                @php $numRows = count($parentsForThis) + 1; @endphp
                @for ($i = 0; $i < $numRows; $i++)
                    <tr>
                        @if ($i === 0)
                            <td rowspan="{{ count($parentsForThis) + 1 }}">{{ $contestant->getProfile()->getFullName() }}</td>
                        @endif

                        @if ($i < ($numRows - 1))
                            @php $parent = $parentsForThis[$i] @endphp
                            <td>{{ $parent->getFullName() }}</td>
                            <td>{{ $parent->email }}</td>
                            <td>
                                <button data-id="{{ $parent->id }}" data-csrf-token="{{ $resetPasswordToken }}" title="Nieuw wachtwoord opsturen" class="btn btn-sm btn-outline-cyndaron gpm-reset-password"><span class="glyphicon glyphicon-repeat"></span></button>
                                <button data-id="{{ $parent->id }}" data-csrf-token="{{ $deleteFromToken }}" title="Ontkoppelen" class="btn btn-sm btn-warning gpm-delete-from-parent-account" data-user-id="{{ $parent->id }}" data-member-id="{{ $contestant->id }}"><span class="glyphicon glyphicon-trash"></span></button>
                            </td>
                        @else
                                <td colspan="3">
                                    <form method="post" action="/contest/addToParentAccount" class="form-inline">
                                        <input type="hidden" name="csrfToken" value="{{ $addToToken }}">
                                        <input type="hidden" name="memberId" value="{{ $contestant->id }}">
                                        Toevoegen:&nbsp;<select name="userId" class="custom-select">
                                            @foreach ($parents as $parentRecord)
                                                @if (in_array($parentRecord, $parentsForThis, true)) @continue @endif
                                                <option value="{{ $parentRecord->id }}">{{ $parentRecord->getFullName() }}</option>
                                            @endforeach
                                        </select>
                                        <input type="submit" class="btn btn-primary" value="Opslaan">
                                    </form>
                                </td>
                        @endif
                    </tr>
                @endfor
            @endforeach
        </tbody>

    </table>

    @component('View/Widget/Modal', ['id' => 'gpm-edit-dialog', 'title' => 'Ouderaccount toevoegen/bewerken', 'sizeClass' => 'modal-lg'])
        @slot('body')
            <input type="hidden" id="gpm-edit-id" name="id" value="">
            <input type="hidden" id="gpm-edit-csrfToken" name="id" value="{{ \Cyndaron\User\UserSession::getCSRFToken('contest', 'createParentAccount') }}">

            @include('View/Widget/Form/BasicInput', ['id' => 'gpm-edit-firstName', 'label' => 'Voornaam', 'required' => false])
            @include('View/Widget/Form/BasicInput', ['id' => 'gpm-edit-initials', 'label' => 'Initialen', 'required' => true])
            @include('View/Widget/Form/BasicInput', ['id' => 'gpm-edit-tussenvoegsel', 'label' => 'Tussenvoegsel', 'required' => false])
            @include('View/Widget/Form/BasicInput', ['id' => 'gpm-edit-lastName', 'label' => 'Achternaam', 'required' => true])
            @include('View/Widget/Form/BasicInput', ['id' => 'gpm-edit-email', 'label' => 'E-mailadres', 'required' => true])
            @include('View/Widget/Form/Checkbox',   ['id' => 'gpm-edit-sendIntroductionMail', 'label' => 'Introductiemail sturen', 'checked' => true])
        @endslot
        @slot('footer')
            <button type="button" class="btn btn-success" id="gpm-edit-save">Opslaan</button>
            <button type="button" class="btn btn-outline-cyndaron" data-toggle="modal" data-target="#gpm-edit-dialog">Annuleren</button>
        @endslot
    @endcomponent
@endsection
