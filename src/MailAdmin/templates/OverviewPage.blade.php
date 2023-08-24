@extends ('Index')

@section ('contents')
    <h2>Domeinen</h2>
    <ul>
        @foreach ($domains as $domain)
            <li>{{ $domain['name'] }}</li>
        @endforeach
    </ul>
    <form method="post" action="/mailadmin/addDomain">
        <input type="hidden" name="csrfToken" value="{{ $csrfTokenAddDomain }}"/>
        <label for="add-domain-name">
            Nieuw domein:
        </label>
        <input type="text" id="add-domain-name" name="domain" pattern=".{2,}\..{2}" required />
        <input type="submit" class="btn btn-primary" value="Aanmaken">
    </form>

    <h2>E-mailadressen</h2>
    <ul>
        @foreach ($users as $user)
            <li>{{ $user['email'] }}</li>
        @endforeach
    </ul>

    <form method="post" action="/mailadmin/addEmail">
        <input type="hidden" name="csrfToken" value="{{ $csrfTokenAddEmail }}"/>
        <label for="add-email-name">
            Gebruikersnaam:
        </label>
        <input type="text" id="add-email-name" name="user" required />
        <label for="add-email-domain">
            Domeinnaam:
        </label>
        <select id="add-email-domain" name="domainId">
            @foreach ($domains as $domain)
                <option value="{{ $domain['id'] }}">{{ $domain['name'] }}</option>
            @endforeach
        </select>
        <label for="add-email-password">
            Wachtwoord:
        </label>
        <input type="password" id="add-email-password" name="password" required />

        <input type="submit" class="btn btn-primary" value="Aanmaken">
    </form>

    <h2>Aliassen</h2>
    <ul>
        @foreach ($aliases as $alias)
            <li>{{ $alias['source'] }} -> {{ $alias['destination'] }}</li>
        @endforeach
    </ul>
@endsection
