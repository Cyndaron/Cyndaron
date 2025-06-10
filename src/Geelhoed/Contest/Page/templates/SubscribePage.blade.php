@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Member\Member $member */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Member\MemberRepository $memberRepository */ @endphp
    @php /** @var \Cyndaron\Geelhoed\Graduation\Graduation[] $graduations */ @endphp
    <form method="post" action="/contest/subscribe/{{ $contest->id }}">
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('contest', 'subscribe') }}"/>
        <input type="hidden" name="memberId" value="{{ $member->id }}"/>
        @component ('View/Widget/Form/FormWrapper', ['label' => 'Naam'])
            @slot('right')
                {{ $member->profile->getFullName() }}
            @endslot
        @endcomponent
        @php $sport = $contest->sport; $sportName = strtolower($sport->name); @endphp
        @component ('View/Widget/Form/FormWrapper', ['id' => 'graduationId', 'label' => "Band {$sportName}"])
            @slot('right')
                <select id="graduationId" name="graduationId" class="form-control custom-select" required>
                    @foreach ($graduations as $graduation)
                        @php $highestGrad = $memberRepository->getHighestGraduation($member, $sport) @endphp
                        <option value="{{ $graduation->id }}" @if ($highestGrad !== null && $highestGrad->id === $graduation->id) selected @endif>{{ $graduation->name }}</option>
                    @endforeach
                </select>
            @endslot
        @endcomponent
        @if ($member->jbnNumber === '')
            @component ('View/Widget/Form/FormWrapper', ['id' => 'jbnNumber', 'label' => 'JBN-nummer'])
                @slot('right')
                    <input id="jbnNumber" name="jbnNumber" class="form-control" required>
                @endslot
            @endcomponent
        @endif
        @component ('View/Widget/Form/FormWrapper', ['id' => 'weight', 'label' => 'Gewicht (hele kg)'])
            @slot('right')
                <input id="weight" name="weight" type="number" class="form-control" required>
            @endslot
        @endcomponent
        @include('View/Widget/Form/Textarea', ['id' => 'comments', 'label' => 'Opmerkingen', 'maxLength' => 200])
        @component ('View/Widget/Form/FormWrapper', ['label' => 'Inschrijfgeld'])
            @slot('right')
                {{ $contest->price|euro }}
            @endslot
        @endcomponent

        <div>
            Kloppen alle bovenstaande gegevens? Klik dan op de knop “Inschrijven”.
            <div class="alert alert-warning">Let op: pas na betaling is je inschrijving definitief.</div>
        </div>
        @component ('View/Widget/Form/FormWrapper')
            @slot('right')
                <input type="submit" class="btn btn-lg btn-primary" value="Inschrijven">
            @endslot
        @endcomponent

    </form>
@endsection
