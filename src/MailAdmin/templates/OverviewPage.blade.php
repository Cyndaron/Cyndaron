@extends ('Index')

@section ('contents')
    @component('View/Widget/Toolbar2')
        @slot('right')
            <form method="post" action="/mailadmin/addDomain" class="form form-inline">
                <input type="hidden" name="csrfToken" value="{{ $csrfTokenAddDomain }}"/>
                <label for="add-domain-name">
                    Nieuw domein:
                </label>
                <input type="text" id="add-domain-name" name="domain" class="form-control" pattern=".{2,}\..{2}" required  />
                <input type="submit" class="btn btn-primary" value="Aanmaken">
            </form>
        @endslot
    @endcomponent

    Snel naar:
    @foreach ($addressesPerDomain as $domain)
        <a href="#domain-{{ $domain->id }}">{{ $domain->name }}</a>
    @endforeach

    @php /** @var \Cyndaron\MailAdmin\Domain[] $addressesPerDomain */ @endphp
    @foreach ($addressesPerDomain as $domain)
        <h2 id="domain-{{ $domain->id }}">{{ $domain->name }}</h2>
        @if (count($domain->addresses) > 0)
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Adres</th>
                        <th>Alias voor</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($domain->addresses as $address)
                        @php
                            $isAlias = $address instanceof \Cyndaron\MailAdmin\AliasEntry;
                            $route = $isAlias ? 'deleteAlias' : 'deleteEmail';
                            $csrfToken = $isAlias ? $csrfTokenDeleteAlias : $csrfTokenDeleteEmail;
                        @endphp
                        <tr>
                            <th>{{ $address->getEmail() }}</th>
                            <th>
                                @if ($address instanceof \Cyndaron\MailAdmin\AliasEntry)
                                    {{ $address->destination }}
                                @endif
                            </th>
                            <th>
                                <form method="post" action="/mailadmin/{{ $route }}/{{ $address->id }}#domain-{{ $domain->id }}">
                                    <input type="hidden" name="csrfToken" value="{{ $csrfToken }}">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Verwijderen">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </button>
                                </form>
                            </th>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <a class="btn btn-outline-cyndaron" data-toggle="collapse" href="#new-email-{{ $domain->id }}" role="button" aria-expanded="false" aria-controls="collapseExample">
            Adres toevoegen
        </a>

        <div id="new-email-{{ $domain->id }}" class="collapse">
            <form method="post" action="/mailadmin/addEmail">
                <input type="hidden" name="csrfToken" value="{{ $csrfTokenAddEmail }}"/>
                <input type="hidden" name="domainId" value="{{ $domain->id }}">

                @component('View/Widget/Form/FormWrapper', ['id' => 'add-email-name', 'label' => 'Adres'])
                    @slot('right')
                        <div class="input-group">
                            <input type="text" id="add-email-name" class="form-control" name="user" required />
                            <div class="input-group-append">
                                <span class="input-group-text">{{ '@' . $domain->name }}</span>
                            </div>
                        </div>
                    @endslot
                @endcomponent

                @include('View/Widget/Form/BasicInput', ['type' => 'password', 'id' => 'add-email-password', 'name' => 'password', 'label' => 'Wachtwoord', 'required' => true])

                @component('View/Widget/Form/FormWrapper')
                    @slot('right')
                        <input type="submit" class="btn btn-primary" value="Aanmaken">
                    @endslot
                @endcomponent

            </form>
        </div>

        <a class="btn btn-outline-cyndaron" data-toggle="collapse" href="#new-alias-{{ $domain->id }}" role="button" aria-expanded="false" aria-controls="collapseExample">
            Alias toevoegen
        </a>
        <div id="new-alias-{{ $domain->id }}" class="collapse">
            <form method="post" action="/mailadmin/addAlias">
                <input type="hidden" name="csrfToken" value="{{ $csrfTokenAddAlias }}"/>
                <input type="hidden" name="domainId" value="{{ $domain->id }}">

                @component('View/Widget/Form/FormWrapper', ['id' => 'add-email-name', 'label' => 'Adres'])
                    @slot('right')
                        <div class="input-group">
                            <input type="text" id="add-email-name" class="form-control" name="user" required />
                            <div class="input-group-append">
                                <span class="input-group-text">{{ '@' . $domain->name }}</span>
                            </div>
                        </div>
                    @endslot
                @endcomponent

                @include('View/Widget/Form/BasicInput', ['type' => 'text', 'id' => 'add-email-alias-for', 'name' => 'destination', 'label' => 'Alias voor'])

                @component('View/Widget/Form/FormWrapper')
                    @slot('right')
                        <input type="submit" class="btn btn-primary" value="Aanmaken">
                    @endslot
                @endcomponent

            </form>
        </div>

    @endforeach
@endsection
