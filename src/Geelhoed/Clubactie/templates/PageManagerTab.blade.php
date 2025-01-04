@component('View/Widget/Toolbar')
    @slot('right')
        <button
            type="button"
            id="pm-mail-everyone"
            data-csrf-token="{{ $tokenHandler->get('webwinkel', 'mail-everyone') }}"
            class="btn btn-outline-cyndaron"
        >
            <span class="glyphicon glyphicon-envelope"></span> Iedereen mailen
        </button>
    @endslot
@endcomponent

<table id="gcam-table" class="table table-striped table-bordered pm-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Naam</th>
        <th>E-mail</th>
        <th>Tel.nummer</th>
        <th>Aantal loten</th>
        <th>Geverifieerd</th>
        <th>E-mail gestuurd</th>
        <th>Acties</th>
    </tr>
    </thead>
    <tbody>
        @php /** @var \Cyndaron\Geelhoed\Clubactie\Subscriber[] $subscribers */ @endphp
        @foreach ($subscribers as $subscriber)
            <tr>
                <td>{{ $subscriber->id }}</td>
                <td>{{ $subscriber->getFullName() }}</td>
                <td>{{ $subscriber->email }}</td>
                <td>{{ $subscriber->phone }}</td>
                <td id="num-sold-tickets-{{ $subscriber->id }}">{{ $subscriber->numSoldTickets }}</td>
                <td id="verified-status-{{ $subscriber->id }}">{{ $subscriber->soldTicketsAreVerified|boolToDingbat }}</td>
                <td id="mail-sent-{{ $subscriber->id }}">{{ $subscriber->emailSent|boolToDingbat }}</td>
                <td>
                    <form method="post" action="/webwinkel/send-mail/{{ $subscriber->hash }}">
                        <button type="submit" class="btn btn-outline-cyndaron" title="Accountgegevens mailen">
                            <span class="glyphicon glyphicon-envelope"></span>
                        </button>
                    </form>
                    @if (!$subscriber->soldTicketsAreVerified)
                        <button class="btn btn-outline-cyndaron pm-confirm-tickets" data-id="{{ $subscriber->id }}" data-toggle="modal" data-target="#pm-confirm-tickets">
                            <span class="glyphicon glyphicon-check" title="Lotenaantal bevestigen"></span>
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@component('View/Widget/Modal',  ['id' => 'pm-confirm-tickets', 'title' => 'Loten bevestigen', 'sizeClass' => 'modal-lg'])
    @slot('body')
        <form id="pm-confirm-tickets-form">
            @include('View/Widget/Form/Number', ['id' => 'num-tickets', 'label' => 'Aantal loten', 'required' => true, 'value' => 0, 'step' => 1])
            <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('clubactie', 'confirm-tickets') }}">
            <div id="pm-confirm-tickets-form-container"></div>
        </form>
    @endslot
    @slot('footer')
        <button id="pm-confirm-tickets-save" type="button" class="btn btn-primary">Opslaan</button>
        <button type="button" class="btn btn-outline-cyndaron" data-dismiss="modal" data-target="#pm-confirm-tickets">Annuleren</button>
    @endslot
@endcomponent
