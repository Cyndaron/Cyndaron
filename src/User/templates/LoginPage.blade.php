@extends('Index')

@section('contents')
    <form class="form-horizontal" method="post" action="#">
        <p>{{ $t->get('Als u inloggegevens hebt gekregen voor deze website, dan kunt u hieronder inloggen.') }}</p>

        @include('View/Widget/Form/InputText', ['id' => 'login_user', 'label' => $t->get('Gebruikersnaam of e-mailadres'), 'required' => true])
        @include('View/Widget/Form/Password', ['id' => 'login_pass', 'label' => $t->get('Wachtwoord'), 'required' => true])

        <input type="hidden" name="csrfToken" value="{{ $csrfToken }}"/>
        <input type="submit" class="btn btn-primary" name="submit" value="{{ $t->get('Inloggen') }}" />
    </form>
@endsection
