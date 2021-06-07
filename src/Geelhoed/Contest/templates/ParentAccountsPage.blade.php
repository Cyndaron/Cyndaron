@extends ('Index')

@section ('contents')
    @component('View/Widget/Toolbar2')
        @slot('right')
            <button id="gpam-new" class="btn btn-success" data-toggle="modal" data-target="#gpm-edit-dialog">Toevoegen</button>
        @endslot
    @endcomponent
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Beheert</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\User\User[] $users */ @endphp
            @php $deleteToken = \Cyndaron\User\User::getCSRFToken('contest', 'deleteParentAccount') @endphp
            @php $deleteFromToken = \Cyndaron\User\User::getCSRFToken('contest', 'deleteFromParentAccount') @endphp
            @php $addToToken = \Cyndaron\User\User::getCSRFToken('contest', 'addToParentAccount') @endphp
            @php $resetPasswordToken = \Cyndaron\User\User::getCSRFToken('user', 'resetpassword') @endphp
            @php $contestants = \Cyndaron\Geelhoed\Member\Member::fetchAll(['isContestant = 1'], [], 'ORDER BY lastname') @endphp
            @foreach ($users as $user)
                @php $controlledMembers = \Cyndaron\Geelhoed\Member\Member::fetchAllByUser($user) @endphp
                @php $controlledMemberIds = array_map(static function($member) { return $member->id; }, $controlledMembers) @endphp
                <tr>
                    <td>{{ $user->getFullName() }}</td>
                    <td>
                        <ul>
                            @foreach ($controlledMembers as $member)
                                <li>{{ $member->getProfile()->getFullName() }} <button class="btn btn-danger btn-sm gpm-delete-from-parent-account" data-user-id="{{ $user->id }}" data-member-id="{{ $member->id }}" data-csrf-token="{{ $deleteFromToken }}"><span class="glyphicon glyphicon-trash"></span></button></li>
                            @endforeach
                        </ul>
                        <form method="post" action="/contest/addToParentAccount" class="form-inline">
                            <input type="hidden" name="csrfToken" value="{{ $addToToken }}">
                            <input type="hidden" name="userId" value="{{ $user->id }}">
                            Toevoegen: <select name="memberId" class="custom-select">
                                @foreach ($contestants as $contestant)
                                    @if (in_array($contestant->id, $controlledMemberIds, true)) @continue @endif
                                    <option value="{{ $contestant->id }}">{{ $contestant->getProfile()->getFullName() }}</option>
                                @endforeach
                            </select>
                            <input type="submit" class="btn btn-primary" value="Opslaan">
                        </form>

                    </td>
                    <td>
                        <button data-id="{{ $user->id }}" data-csrf-token="{{ $resetPasswordToken }}" class="btn btn-sm btn-outline-cyndaron gpm-reset-password"><span class="glyphicon glyphicon-repeat"></span></button>
                        <button data-id="{{ $user->id }}" data-csrf-token="{{ $deleteToken }}" class="btn btn-sm btn-danger gpm-delete-parent-account"><span class="glyphicon glyphicon-trash"></span></button>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

    @component('View/Widget/Modal', ['id' => 'gpm-edit-dialog', 'title' => 'Ouderaccount toevoegen/bewerken', 'sizeClass' => 'modal-lg'])
        @slot('body')
            <input type="hidden" id="gpm-edit-id" name="id" value="">
            <input type="hidden" id="gpm-edit-csrfToken" name="id" value="{{ \Cyndaron\User\User::getCSRFToken('contest', 'createParentAccount') }}">

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
