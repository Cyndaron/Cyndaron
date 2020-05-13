@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Contest\Contest $contest */@endphp

    @component('Widget/Toolbar')
        @slot('right')
            @include('Widget/Button', ['kind' => 'th', 'link' => '/contest/subscriptionListExcel/' . $contest->id, 'text' => 'Excel-export'])
        @endslot
    @endcomponent

    <table id="gcsm-table" data-csrf-token-delete="{{ \Cyndaron\User\User::getCSRFToken('contest', 'removeSubscription') }}" class="table table-striped table-bordered pm-table">
        <thead>
            <tr>
                <th></th>
                <th>Naam</th>
                <th>Band</th>
                <th>Gewicht</th>
                <th>JBN-nummer</th>
                <th>Betaald</th>
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
                    <td>{{ \Cyndaron\Util::boolToText($contestMember->isPaid) }}</td>
                    <td>
                        <button class="btn btn-danger gcsm-delete" data-id="{{ $contestMember->id }}" title="Deze inschrijving verwijderen"><span class="glyphicon glyphicon-trash"></span></button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection