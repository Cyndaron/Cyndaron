@extends('Index')

@section('contents')
    <form method="post" action="/user/changePassword">
        <p>U kunt hier uw wachtwoord wijzigen. Let op: het wachtwoord moet minstens 8 tekens lang zijn.</p>
        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
        @include('Widget/Form/BasicInput', ['type' => 'password', 'id' => 'oldPassword', 'label' => 'Oud wachtwoord'])
        @include('Widget/Form/BasicInput', ['type' => 'password', 'id' => 'newPassword', 'label' => 'Nieuw wachtwoord'])
        @include('Widget/Form/BasicInput', ['type' => 'password', 'id' => 'newPasswordRepeat', 'label' => 'Nieuw wachtwoord herhalen'])
        @component('Widget/Form/FormWrapper')
            @slot('right')
                <input type="submit" class="btn btn-primary" value="Opslaan"
            @endslot
        @endcomponent
    </form>
@endsection