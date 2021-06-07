@extends ('Index')

@section ('contents')
    @php /** @var \Cyndaron\Geelhoed\Contest\ContestMember $contestMember */ @endphp
    <form method="post" action="/contest/editSubscription/{{ $contestMember->id }}">
        <input type="hidden" name="csrfToken" value="{{ \Cyndaron\User\User::getCSRFToken('contest', 'editSubscription') }}">
        @component ('View/Widget/Form/FormWrapper', ['label' => 'Naam'])
            @slot('right'){{ $contestMember->getMember()->getProfile()->getFullName() }}@endslot
        @endcomponent
        @include('View/Widget/Form/Number', ['id' => 'weight', 'label' => 'Gewicht', 'required' => true, 'value' => $contestMember->weight])
        @include('View/Widget/Form/Select', ['id' => 'graduationId', 'label' => 'Band', 'required' => true, 'selected' => $contestMember->graduationId, 'options' => $graduations])

        <input type="submit" value="Opslaan" class="btn btn-primary">
    </form>
@endsection
