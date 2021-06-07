@extends ('Index')

@section ('contents')

    Openstaand inschrijfgeld: {{ $due|euro }}
    @if ($due > 0.0)
        <a class="btn btn-primary" href="/contest/payFullDue">Betalen</a>
    @endif

    @php /** @var \Cyndaron\Geelhoed\Contest\Contest[] $contests */ @endphp
    @foreach ($contests as $contest)
        @php $canChange = $contest->registrationCanBeChanged($profile); @endphp
        <div class="card location-card">
            <div class="card-header">
                <h2><a href="/contest/view/{{ $contest->id }}">{{ $contest->name }}</a></h2>
            </div>
            <div class="card-body">
                <table>
                    <tr><th>Locatie:</th><td>{{ $contest->location }}</td></tr>
                    <tr><th>Data: </th><td>
                            <ul>
                                @foreach ($contest->getDates() as $contestDate)
                                    @php $classes = $contestDate->getClasses() @endphp
                                    <li>
                                    {{ $contestDate->datetime|dmyHm }}@if (count($classes) > 0):@endif
                                    @foreach($classes as $class)
                                        {{ $class->name }}@if (!$loop->last), @endif
                                    @endforeach
                                    </li>
                                @endforeach
                            </ul>

                        </td>
                    </tr>
                    <tr><th>Inschrijfgeld:</th><td>{{ $contest->price|euro }}</td></tr>
                </table>
                <a role="button" class="btn btn-outline-cyndaron" href="/contest/view/{{ $contest->id }}">Meer informatie</a>

                <h6 class="mt-3">Ingeschreven</h6>

                <table class="table table-bordered table-striped">
                    <tr>
                        <th>Naam</th>
                        <th>Gewicht</th>
                        <th>Band</th>
                        <th>Status</th>
                        @if ($canChange)<th>Aanpassen</th>@endif
                    </tr>

                    @foreach (\Cyndaron\Geelhoed\Contest\ContestMember::fetchAllByContestAndMembers($contest, $controlledMembers) as $contestMember)
                        <tr>
                            <td>{{ $contestMember->getMember()->getProfile()->getFullName() }}</td>
                            <td>{{ $contestMember->weight }} kg</td>
                            <td>{{ $contestMember->getGraduation()->name }}</td>
                            <td>
                                @if ($contestMember->isPaid)Betaald
                                @elseif(time() < strtotime($contest->registrationDeadline))Niet betaald
                                @else Verlopen
                                @endif
                            </td>
                            @if ($canChange)
                                <td>
                                    <a href="/contest/editSubscription/{{ $contestMember->id }}" class="btn btn-warning" title="Gewicht of band veranderd? Geef de wijziging hier door!">
                                        <span class="glyphicon glyphicon-pencil"></span>
                                    </a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endforeach
@endsection