@extends ('Index')

@section ('contents')
    @component('View/Widget/Toolbar')
        @slot('left')
            <a href="/pagemanager/member" class="btn btn-outline-cyndaron">« Terug naar Ledenbeheer</a>
        @endslot
    @endcomponent

    <table class="table table-bordered table-striped">
        <tr>
            <th>IBAN</th>
            <th>Rek.houder</th>
            <th>Bedrag</th>
            <th>Leden</th>
        </tr>
        @php /** @var \Cyndaron\Geelhoed\Member\DirectDebit[] $directDebits */ @endphp
        @php /** @var \Cyndaron\Geelhoed\Member\MemberRepository $memberRepository */ @endphp
        @foreach ($directDebits as $directDebit)
            <tr>
                <td>{{ $directDebit->iban }}</td>
                <td>{{ $directDebit->ibanHolder }}</td>
                <td>{{ $directDebit->getTotalQuarterlyFee($memberRepository)|euro }}</td>
                <td>
                    <ul>
                        @foreach ($directDebit->members as $member)
                            <li>{{ $member->profile->getFullName() }}: {{ $memberRepository->getQuarterlyFee($member)|euro }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endforeach
    </table>
@endsection
