@extends('Index')

@section('contents')
    Onze sportschool zou niet bestaan zonder de inzet van onze onvermoeibare vrijwilligers. Interesse om mee te helpen?
    Dan kun je je hier inschrijven.

    <h2>Je gegevens</h2>
    @include('View/Widget/Form/BasicInput', ['id' => 'gegevens-naam', 'label' => 'Naam', 'required' => true])
    @include('View/Widget/Form/BasicInput', ['id' => 'gegevens-email', 'label' => 'E-mailadres', 'type' => 'email', 'required' => true])
    @include('View/Widget/Form/BasicInput', ['id' => 'gegevens-telefoon', 'label' => 'Telefoonnummer', 'type' => 'phone'])

    <h2>Hulp bij het Try-Out-toernooi/clubkampioenschappen</h2>
    Voor het try-out-toernooi hebben we veel vrijwilligers nodig. Zo zitten achter elke tafel twee medewerkers
    (met tien tafels betekent dat dus twintig medewerkers) en krijgt elk poultje een groepjesbegeleider (onder de 16).
    Daarnaast zijn er nog andere functies zoals het bemannen van de kassa en de weegschalen.

    @include ('View/Widget/Form/Checkbox', [
        'id' => 'toernooien-groepjesbegeleiden',
        'description' => 'Ik wil graag helpen als groepjesbegeleider',
    ])
    @include ('View/Widget/Form/Checkbox', [
        'id' => 'toernooien-tafelmedewerker',
        'description' => 'Ik wil graag helpen achter de tafel',
    ])
    @include ('View/Widget/Form/Checkbox', [
        'id' => 'toernooien-tafelmedewerker',
        'description' => 'Ik wil graag helpen als EHBO’er',
    ])
    @include ('View/Widget/Form/Checkbox', [
        'id' => 'toernooien-entree',
        'description' => 'Ik wil graag helpen bij de kassa/bij het wegen',
    ])

    <h2>Hulp bij andere zaken</h2>
    @include ('View/Widget/Form/Checkbox', [
        'id' => 'anderezaken-schilderen',
        'description' => 'Ik wil graag helpen bij schilderwerkzaamheden',
    ])
    @include ('View/Widget/Form/Checkbox', [
        'id' => 'anderezaken-autoonderhoud',
        'description' => 'Ik wil graag helpen bij auto-onderhoud',
    ])


@endsection
