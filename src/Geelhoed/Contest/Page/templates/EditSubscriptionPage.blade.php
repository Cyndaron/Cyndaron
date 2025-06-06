@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Contest\Model\ContestMember $contestMember */ @endphp
    <form method="post" action="/contest/editSubscription/{{ $contestMember->id }}">
        <input type="hidden" name="csrfToken" value="{{ $tokenHandler->get('contest', 'editSubscription') }}">
        @component ('View/Widget/Form/FormWrapper', ['label' => 'Naam'])
            @slot('right')
                {{ $contestMember->member->profile->getFullName() }}
            @endslot
        @endcomponent
        @include('View/Widget/Form/Number', ['id' => 'weight', 'label' => 'Gewicht', 'required' => true, 'value' => $contestMember->weight])
        @include('View/Widget/Form/Select', ['id' => 'graduationId', 'label' => 'Band', 'required' => true, 'selected' => $contestMember->graduation->id, 'options' => $graduations])

        <input type="submit" value="Opslaan" class="btn btn-primary">
    </form>
@endsection
