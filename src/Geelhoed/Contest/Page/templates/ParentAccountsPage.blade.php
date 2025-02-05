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
                <th>E-mailadres</th>
                <th>Beheert</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\User\User[] $users */ @endphp
            @php $deleteToken = $tokenHandler->get('contest', 'deleteParentAccount') @endphp
            @php $deleteFromToken = $tokenHandler->get('contest', 'deleteFromParentAccount') @endphp
            @php $addToToken = $tokenHandler->get('contest', 'addToParentAccount') @endphp
            @php $resetPasswordToken = $tokenHandler->get('user', 'resetpassword') @endphp
            @php /** @var \Cyndaron\Geelhoed\Member\Member[] $contestants */ @endphp
            @php /** @var \Cyndaron\Geelhoed\Member\MemberRepository $memberRepository */ @endphp
            @foreach ($users as $user)
                @php $controlledMembers = $memberRepository->fetchAllByUser($user) @endphp
                @php $controlledMemberIds = array_map(static function($member) { return $member->id; }, $controlledMembers) @endphp
                <tr>
                    <td>{{ $user->getFullName() }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <ul>
                            @foreach ($controlledMembers as $member)
                                <li>{{ $member->profile->getFullName() }} <button title="Ontkoppelen" class="btn btn-warning btn-sm gpm-delete-from-parent-account" data-user-id="{{ $user->id }}" data-member-id="{{ $member->id }}" data-csrf-token="{{ $deleteFromToken }}"><span class="glyphicon glyphicon-trash"></span></button></li>
                            @endforeach
                        </ul>
                        <form method="post" action="/contest/addToParentAccount" class="form-inline">
                            <input type="hidden" name="csrfToken" value="{{ $addToToken }}">
                            <input type="hidden" name="userId" value="{{ $user->id }}">
                            Toevoegen: <select name="memberId" class="custom-select">
                                @foreach ($contestants as $contestant)
                                    @if (in_array($contestant->id, $controlledMemberIds, true)) @continue @endif
                                    <option value="{{ $contestant->id }}">{{ $contestant->profile->getFullName() }}</option>
                                @endforeach
                            </select>
                            <input type="submit" class="btn btn-primary" value="Opslaan">
                        </form>

                    </td>
                    <td>
                        <button data-id="{{ $user->id }}" data-csrf-token="{{ $resetPasswordToken }}" title="Nieuw wachtwoord opsturen" class="btn btn-sm btn-outline-cyndaron gpm-reset-password"><span class="glyphicon glyphicon-repeat"></span></button>
                        <button data-id="{{ $user->id }}" data-csrf-token="{{ $deleteToken }}" title="Account verwijderen(!)" class="btn btn-sm btn-danger gpm-delete-parent-account"><span class="glyphicon glyphicon-trash"></span></button>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>

    @component('View/Widget/Modal', ['id' => 'gpm-edit-dialog', 'title' => 'Ouderaccount toevoegen/bewerken', 'sizeClass' => 'modal-lg'])
        @slot('body')
            <input type="hidden" id="gpm-edit-id" name="id" value="">
            <input type="hidden" id="gpm-edit-csrfToken" name="id" value="{{ $tokenHandler->get('contest', 'createParentAccount') }}">

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
