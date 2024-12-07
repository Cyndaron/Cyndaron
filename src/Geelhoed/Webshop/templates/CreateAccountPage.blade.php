@extends ('Index')

@section ('contents')
    <form method="post">
        @component ('View/Widget/Form/FormWrapper', ['id' => 'firstName', 'label' => 'Voornaam'])
            @slot('right')
                <input id="firstName" name="firstName" class="form-control" required>
            @endslot
        @endcomponent
        @component ('View/Widget/Form/FormWrapper', ['id' => 'tussenvoegsel', 'label' => 'Tussenvoegsel'])
            @slot('right')
                <input id="tussenvoegsel" name="tussenvoegsel" class="form-control">
            @endslot
        @endcomponent
        @component ('View/Widget/Form/FormWrapper', ['id' => 'lastName', 'label' => 'Achternaam'])
            @slot('right')
                <input id="lastName" name="lastName" class="form-control" required>
            @endslot
        @endcomponent
        @component ('View/Widget/Form/FormWrapper', ['id' => 'email', 'label' => 'E-mailadres'])
            @slot('right')
                <input id="email" name="email" class="form-control" type="email" required>
            @endslot
        @endcomponent
        <input type="submit" class="btn btn-primary" value="Verzenden"/>
    </form>
@endsection
