@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\Contest $contest */@endphp
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\ContestDateRepository $contestDateRepository */@endphp
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository $contestMemberRepository */@endphp

    @component('View/Widget/Toolbar')
        @slot('right')
            @include('View/Widget/Button', ['kind' => 'th', 'link' => '/contest/subscriptionListExcel/' . $contest->id, 'text' => 'Excel-export'])
        @endslot
    @endcomponent

    <table id="gcsm-table" class="table table-striped table-bordered pm-table"
           data-csrf-token-delete="{{ $tokenHandler->get('contest', 'removeSubscription') }}"
           data-csrf-token-update-payment-status="{{ $tokenHandler->get('contest', 'updatePaymentStatus') }}"
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
        @foreach ($contestMemberRepository->fetchAllByContest($contest) as $contestMember)
            @php $member = $contestMember->member @endphp
            @php $profile = $member->profile @endphp
            <tr>
                <td></td>
                <td>{{ $profile->getFullName() }}</td>
                <td>{{ $profile->getGenderDisplay() }}</td>
                <td>
                    {{ $profile->street }} {{ $profile->houseNumber }} {{ $profile->houseNumberAddition }}<br>
                    {{ $profile->postalCode }} {{ $profile->city }}
                </td>
                <td>
                    <abbr title="@if ($profile->dateOfBirth){{ $profile->dateOfBirth->format('d-m-Y') }}@endif">
                        {{ $profile->getAge($contestDateRepository->getFirstByContest($contest)) }}
                    </abbr>
                </td>
                <td>{{ $contestMember->graduation->name }}</td>
                <td>{{ $contestMember->weight }}</td>
                <td>{{ $member->jbnNumber }}</td>
                <td>{{ \Cyndaron\View\Template\ViewHelpers::boolToText($contestMember->isPaid) }}</td>
                <td>{{ $contestMember->molliePaymentId }}</td>
                <td>{{ $contestMember->comments }}</td>
                <td>
                    <div class="btn-group">
                        @if ($contestMember->isPaid)
                            <button class="btn btn-warning gcsm-update-payment-status"
                                    data-id="{{ $contestMember->id }}" data-is-paid="0" title="Markeren als onbetaald">
                                @include('View/Widget/Icon', ['type' => 'money'])</button>
                        @else
                            <button class="btn btn-success gcsm-update-payment-status"
                                    data-id="{{ $contestMember->id }}" data-is-paid="1" title="Markeren als betaald">
                                @include('View/Widget/Icon', ['type' => 'money'])</button>
                        @endif
                        <a href="/contest/editSubscription/{{ $contestMember->id }}" class="btn btn-warning"
                           title="Gewicht of band wijzigen">
                            @include('View/Widget/Icon', ['type' => 'edit'])
                        </a>
                        <button class="btn btn-danger gcsm-delete" data-id="{{ $contestMember->id }}"
                                title="Deze inschrijving verwijderen">@include('View/Widget/Icon', ['type' => 'delete'])
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
