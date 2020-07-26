@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Contest\Contest $contest */@endphp

    @component('Widget/Toolbar')
        @slot('right')
            @include('Widget/Button', ['kind' => 'th', 'link' => '/contest/subscriptionListExcel/' . $contest->id, 'text' => 'Excel-export'])
        @endslot
    @endcomponent

    <table id="gcsm-table" class="table table-striped table-bordered pm-table"
           data-csrf-token-delete="{{ \Cyndaron\User\User::getCSRFToken('contest', 'removeSubscription') }}"
           data-csrf-token-update-payment-status="{{ Cyndaron\User\User::getCSRFToken('contest', 'updatePaymentStatus') }}"
    >
        <thead>
            <tr>
                <th></th>
                <th>Naam</th>
                <th>M/V</th>
                <th>Adres</th>
                <th>Leeftijd</th>
                <th>Band</th>
                <th>Gewicht</th>
                <th>JBN-nummer</th>
                <th>Betaald</th>
                <th>Transactie-ID</th>
                <th>Opmerkingen</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php
                $firstDate = $contest->getFirstDate();
                $contestDateObject = null;
                if ($firstDate !== null):
                    $contestDateObject = new \Safe\DateTime($firstDate);
                endif;
            @endphp
            @foreach ($contest->getContestMembers() as $contestMember)
                @php $member = $contestMember->getMember() @endphp
                @php $profile = $member->getProfile() @endphp
                <tr>
                    <td></td>
                    <td>{{ $profile->getFullName() }}</td>
                    <td>{{ $profile->getGenderDisplay() }}</td>
                    <td>
                        {{ $profile->street }} {{ $profile->houseNumber }} {{ $profile->houseNumberAddition }}<br>
                        {{ $profile->postalCode }} {{ $profile->city }}
                    </td>
                    <td>
                        <abbr title="@if ($profile->dateOfBirth){{ date('d-m-Y', strtotime($profile->dateOfBirth)) }}@endif">
                            {{ $profile->getAge($contestDateObject) }}
                        </abbr>
                    </td>
                    <td>{{ $contestMember->getGraduation()->name }}</td>
                    <td>{{ $contestMember->weight }}</td>
                    <td>{{ $member->jbnNumber }}</td>
                    <td>{{ \Cyndaron\Template\ViewHelpers::boolToText($contestMember->isPaid) }}</td>
                    <td>{{ $contestMember->molliePaymentId }}</td>
                    <td>{{ $contestMember->comments }}</td>
                    <td>
                        <div class="btn-group">
                            @if ($contestMember->isPaid)
                                <button class="btn btn-warning gcsm-update-payment-status" data-id="{{ $contestMember->id }}" data-is-paid="0" title="Markeren als onbetaald"><span class="glyphicon glyphicon-euro"></span></button>
                            @else
                                <button class="btn btn-success gcsm-update-payment-status" data-id="{{ $contestMember->id }}" data-is-paid="1" title="Markeren als betaald"><span class="glyphicon glyphicon-euro"></span></button>
                            @endif
                            <a href="/contest/editSubscription/{{ $contestMember->id }}" class="btn btn-warning" title="Gewicht of band wijzigen">
                                <span class="glyphicon glyphicon-pencil"></span>
                            </a>
                            <button class="btn btn-danger gcsm-delete" data-id="{{ $contestMember->id }}" title="Deze inschrijving verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection