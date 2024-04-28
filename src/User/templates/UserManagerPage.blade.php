@extends('Index')

@section('contents')
    @component('View/Widget/Toolbar')
        @slot('right')
            <button id="um-create-user"
                    data-csrf-token="{{ \Cyndaron\User\UserSession::getCSRFToken('user', 'add') }}"
                    type="button" class="btn btn-success" data-toggle="modal" data-target="#um-edit-user-dialog">
                <span class="glyphicon glyphicon-plus"></span> Nieuwe gebruiker toevoegen
            </button>
        @endslot
    @endcomponent

    <table
            id="um-usertable"
            class="table table-bordered table-striped"
            data-edit-csrf-token="{{ \Cyndaron\User\UserSession::getCSRFToken('user', 'edit') }}"
            data-resetpassword-csrf-token="{{ \Cyndaron\User\UserSession::getCSRFToken('user', 'resetpassword') }}"
            data-delete-csrf-token="{{ \Cyndaron\User\UserSession::getCSRFToken('user', 'delete') }}">
        <thead>
            <tr>
                <th>ID</th>
                <th>Gebruikersnaam</th>
                <th>E-mailadres</th>
                <th>Niveau</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\User\User[] $users */ @endphp
            @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $userLevelDescriptions[$user->level] }}</td>
                <td>
                    <div class="btn-group">
                        <button class="um-edit-user btn btn-sm btn-outline-cyndaron" title="Gebruiker bewerken"
                                data-toggle="modal" data-target="#um-edit-user-dialog"
                                data-id="{{ $user->id }}"
                                data-username="{{ $user->username }}"
                                data-email="{{ $user->email }}"
                                data-level="{{ $user->level }}"
                                data-firstName="{{ $user->firstName }}"
                                data-tussenvoegsel="{{ $user->tussenvoegsel }}"
                                data-lastName="{{ $user->lastName }}"
                                data-role="{{ $user->role }}"
                                data-comments="{{ $user->comments }}"
                                data-avatar="{{ $user->avatar }}"
                                data-hideFromMemberList="{{ $user->hideFromMemberList }}">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </button>
                        <button class="um-resetpassword btn btn-sm btn-outline-cyndaron" data-id="{{ $user->id }}" title="Nieuw wachtwoord instellen">
                            <span class="glyphicon glyphicon-repeat"></span>
                        </button>
                        <button class="um-updateAvatar btn btn-sm btn-outline-cyndaron" data-id="{{ $user->id }}" title="Avatar veranderen" data-toggle="modal" data-target="#um-update-avatar-dialog">
                            <span class="glyphicon glyphicon-picture"></span>
                        </button>
                        <button class="um-delete btn btn-sm btn-danger" data-id="{{ $user->id }}" title="Gebruiker verwijderen">
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @component('View/Widget/Modal', ['id' => 'um-edit-user-dialog', 'title' => 'Gebruiker toevoegen/bewerken', 'sizeClass' => 'modal-lg'])
        @slot('body')
            <input type="hidden" id="um-id" />
            <input type="hidden" id="um-csrf-token" />

            <div class="form-group row">
                <label for="um-username" class="col-sm-2 col-form-label">Gebruikersnaam:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-username">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-email" class="col-sm-2 col-form-label">E-mailadres:</label>
                <div class="col-sm-10">
                    <input type="email" class="form-control" id="um-email">
                </div>
            </div>

            <div class="form-group row" id="um-password-group">
                <label for="um-password" class="col-sm-2 col-form-label">Wachtwoord:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-password" placeholder="Leeglaten voor een willekeurig wachtwoord">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-level" class="col-sm-2 col-form-label">Gebruikersniveau:</label>
                <div class="col-sm-10">
                    <select id="um-level" class="custom-select">
                        <option value="1">{{ $userLevelDescriptions[1] }}</option>
                        <option value="4">{{ $userLevelDescriptions[4] }}</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="um-firstName" class="col-sm-2 col-form-label">Voornaam:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-firstName">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-tussenvoegsel" class="col-sm-2 col-form-label">Tussenvoegsel:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-tussenvoegsel">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-lastName" class="col-sm-2 col-form-label">Achternaam:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-lastName">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-role" class="col-sm-2 col-form-label">Functie:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-role">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-comments" class="col-sm-2 col-form-label">Opmerkingen:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-comments">
                </div>
            </div>

            <div class="form-group row">
                <label for="um-avatar" class="col-sm-2 col-form-label">Foto/avatar:</label>
                <div class="col-sm-10">
                    <input class="form-control" id="um-avatar">
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-12">
                    <input type="checkbox" class="" id="um-hideFromMemberList" value="1">
                    <label class="form-check-label" for="um-hideFromMemberList">Verbergen op Wie-is-wie</label>
                </div>
            </div>
        @endslot
        @slot ('footer')
            <button id="um-edit-user-save" type="button" class="btn btn-primary">Opslaan</button>
            <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal">Annuleren</button>
        @endslot
    @endcomponent
    @component('View/Widget/Modal', ['id' => 'um-update-avatar-dialog', 'title' => 'Avatar aanpassen', 'sizeClass' => 'modal-lg'])
        @slot('body')
            <form id="um-update-avatar" method="post" action="/user/changeAvatar/" enctype="multipart/form-data">
                <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\UserSession::getCSRFToken('user', 'changeAvatar') }}"/>

                @include('View/Widget/Form/File', ['id' => 'avatarFile', 'label' => 'Nieuwe avatar'])
            </form>
        @endslot
        @slot('footer')
            <button type="submit" class="btn btn-primary" form="um-update-avatar">Opslaan</button>
        @endslot

    @endcomponent

@endsection
