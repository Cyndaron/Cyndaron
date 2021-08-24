@php
    /** @var \Cyndaron\Geelhoed\Contest\Contest $contest  */
    /** @var \Cyndaron\Geelhoed\Contest\ContestMember|null $contestMember */
    if ($contestMember !== null):
        $weight = "{$contestMember->weight} kg";
        $graduation = $contestMember->getGraduation();
    else:
        $weight = '';
        $graduation = $member->getHighestGraduation($contest->getSport());
    endif;
    /** @var \Cyndaron\Geelhoed\Member\Member $member */
    $name = $member->getProfile()->getFullName();
    $graduationName = $graduation !== null ? $graduation->name : '-';
    $expired = time() > strtotime($contest->registrationDeadline);
@endphp
<tr id="gcv-member-subscriptions-{{ $member->id }}">
    <td>{{ $name }}</td>
    <td>{{ $graduationName }}</td>
    <td>{{ $weight }}</td>
    <td>
        @if ($contestMember === null)Niet ingeschreven
        @elseif ($contestMember->isPaid)Betaald
        @elseif(!$expired)Niet betaald
        @else Verlopen
        @endif
    </td>
    <td>
        @if ($contestMember !== null)
            @if ($canChange)
                <a href="/contest/editSubscription/{{ $contestMember->id }}" class="btn btn-warning" title="Gewicht of band veranderd? Geef de wijziging hier door!">
                    <span class="glyphicon glyphicon-pencil"></span>
                </a>
            @endif
            @if (!$contestMember->isPaid)
                <button
                    class="gcv-cancel-subscription btn btn-danger"
                    data-member-id="{{ $member->id }}"
                    data-contest-member-id="{{ $contestMember->id }}"
                    data-csrf-token="{{ $csrfToken }}"
                >Annuleren</button>
            @endif
        @elseif (!$expired)
            <a href="/contest/subscribe/{{ $contest->id }}/{{ $member->id }}" class="btn btn-primary">Inschrijven</a>
        @endif
    </td>
</tr>
