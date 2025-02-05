@extends ('Index')

@section ('contents')
    <div class="alert alert-info">
        <h2>Bestellen is vanaf nu mogelijk</h2>
        <p>Gebruik de link uit de e-mail om te bestellen.</p>
    </div>


    <h2>Geen e-mail ontvangen?</h2>
    <p>Geen probleem. <a href="/webwinkel/account-aanmaken?reden=geen-email">Klik hier</a>, vul je gegevens in en je krijgt
        (na controle) een e-mail met een bestellink.</p>

    <h2>Geen loten verkocht, maar wel bestellen?</h2>
    <p>
        Ook geen probleem. <a href="/webwinkel/account-aanmaken?reden=geen-loten">Klik hier</a> om te beginnen.
    </p>
@endsection
