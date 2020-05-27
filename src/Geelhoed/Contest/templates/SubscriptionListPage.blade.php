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
                <th>Band</th>
                <th>Gewicht</th>
                <th>JBN-nummer</th>
                <th>Betaald</th>
                <th>Opmerkingen</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($contest->getContestMembers() as $contestMember)
                @php $member = $contestMember->getMember() @endphp
                <tr>
                    <td></td>
                    <td>{{ $member->getProfile()->getFullName() }}</td>
                    <td>{{ $contestMember->getGraduation()->name }}</td>
                    <td>{{ $contestMember->weight }}</td>
                    <td>{{ $member->jbnNumber }}</td>
                    <td>{{ \Cyndaron\Template\ViewHelpers::boolToText($contestMember->isPaid) }}</td>
                    <td>{{ $contestMember->comments }}</td>
                    <td>
                        <div class="btn-group">
                            @if ($contestMember->isPaid)
                                <button class="btn btn-warning gcsm-update-payment-status" data-id="{{ $contestMember->id }}" data-is-paid="0" title="Markeren als onbetaald"><span class="glyphicon glyphicon-euro"></span></button>
                            @else
                                <button class="btn btn-success gcsm-update-payment-status" data-id="{{ $contestMember->id }}" data-is-paid="1" title="Markeren als betaald"><span class="glyphicon glyphicon-euro"></span></button>
                            @endif
                            <button class="btn btn-danger gcsm-delete" data-id="{{ $contestMember->id }}" title="Deze inschrijving verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection