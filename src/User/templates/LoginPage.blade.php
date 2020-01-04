@extends('Index')

@section('contents')
    <form class="form-horizontal" method="post" action="#">
        <p>Als u inloggegevens hebt gekregen voor deze website, dan kunt u hieronder inloggen.</p>

        @include('Widget/Form/InputText', ['id' => 'login_user', 'label' => 'Gebruikersnaam of e-mailadres', 'required' => true])
        @include('Widget/Form/Password', ['id' => 'login_pass', 'label' => 'Wachtwoord', 'required' => true])

        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}"/>
        <input type="submit" class="btn btn-primary" name="submit" value="Inloggen" />
    </form>
@endsection