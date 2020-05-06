@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Contest\Contest $contest */@endphp
    <table class="table table-striped table-bordered pm-table">
        <thead>
            <tr>
                <th></th>
                <th>Naam</th>
                <th>Band</th>
                <th>Gewicht</th>
                <th>JBN-nummer</th>
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
                    <td></td>
                </tr>
            @endforeach
        </tbody>

    </table>
@endsection