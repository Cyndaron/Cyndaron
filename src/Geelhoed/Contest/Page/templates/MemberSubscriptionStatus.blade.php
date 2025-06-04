@php
    /** @var \Cyndaron\Geelhoed\Contest\Model\Contest $contest  */
    /** @var \Cyndaron\Geelhoed\Contest\Model\ContestMember|null $contestMember */
    /** @var \Cyndaron\Geelhoed\Member\Member $member */
    /** @var \Cyndaron\Geelhoed\Member\MemberRepository $memberRepository */
    if ($contestMember !== null):
        $weight = "{$contestMember->weight} kg";
        $graduation = $contestMember->graduation;
    else:
        $weight = '';
        $graduation = $memberRepository->getHighestGraduation($member, $contest->sport);
    endif;
    /** @var \Cyndaron\Geelhoed\Member\Member $member */
    $name = $member->profile->getFullName();
    $graduationName = $graduation !== null ? $graduation->name : '-';
    $expired = time() > strtotime($contest->registrationDeadline);
    $subTitleParts = [];
    if ($graduation !== null)
        $subTitleParts[] = $graduationName;
    if (!empty($weight))
        $subTitleParts[] = $weight;
@endphp
<div id="gcv-member-subscriptions-{{ $member->id }}" class="card gcv-member-subscription">
    <div class="card-header">
        <h4>{{ $name }}</h4>
    </div>
    <div class="card-body">
        <p>{{ implode(', ', $subTitleParts) }}</p>
        <b>Status: </b>
        @if ($contestMember === null)
            Niet ingeschreven
        @elseif ($contestMember->isPaid)
            Betaald
        @elseif(!$expired)
            Niet betaald
        @else
            Verlopen
        @endif

        <div>
            @if ($contestMember !== null)
                @if ($canChange)
                    <a href="/contest/editSubscription/{{ $contestMember->id }}" class="btn btn-warning"
                       title="Gewicht of band veranderd? Geef de wijziging hier door!">
                        Aanpassen
                    </a>
                @endif
                @if (!$contestMember->isPaid)
                    <button
                        class="gcv-cancel-subscription btn btn-danger"
                        data-member-id="{{ $member->id }}"
                        data-contest-member-id="{{ $contestMember->id }}"
                        data-csrf-token="{{ $csrfToken }}"
                    >Annuleren
                    </button>
                @endif
            @elseif (!$expired)
                <a href="/contest/subscribe/{{ $contest->id }}/{{ $member->id }}"
                   class="btn btn-primary">Inschrijven</a>
            @endif
        </div>
    </div>
</div>
