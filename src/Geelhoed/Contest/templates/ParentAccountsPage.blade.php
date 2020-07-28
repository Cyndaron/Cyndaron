@extends ('Index')

@section ('contents')
    @component('Widget/Toolbar2')
        @slot('right')

        @endslot
    @endcomponent
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Beheert</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            @php /** @var \Cyndaron\User\User[] $users */ @endphp
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->getFullName() }}</td>
                    <td>
                        <ul>
                            @foreach (\Cyndaron\Geelhoed\Member\Member::fetchAllByUser($user) as $member)
                                <li>{{ $member->getProfile()->getFullName() }}</li>
                            @endforeach
                        </ul>

                    </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>

    </table>
@endsection