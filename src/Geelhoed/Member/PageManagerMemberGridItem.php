<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\View\Template\ViewHelpers;
use function array_values;
use function assert;
use function str_replace;

final class PageManagerMemberGridItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $streetAddress,
        public readonly string $postcodeAndCity,
        public readonly string $email,
        /** @var string[] */
        public readonly array $phoneNumbers,
        /** @var string[] */
        public readonly array $hours,
        public readonly string $iban,
        public readonly string $quarterlyFee,
        public readonly int $isContestant,
        public readonly int $canLogin,
        public readonly int $isSenior,

        // Search data
        public readonly string $gender,
        public readonly int $temporaryStop,
        public readonly string $paymentMethod,
        public readonly int $paymentProblem,
        public readonly string $dateOfBirth,
        /** @var int[] */
        public readonly array $sports,
        /** @var int[] */
        public readonly array $graduations,
        /** @var int[] */
        public readonly array $locations,
    ) {
    }

    /**
     * @param Sport[] $allSports
     */
    public static function createFromMember(MemberRepository $repository, Member $member, array $allSports): self
    {
        $profile = $member->profile;
        $name = str_replace('  ', ' ', "{$profile->lastName} {$profile->tussenvoegsel} {$profile->firstName}");
        $houseNumber = $profile->houseNumber ?: '';
        $streetAddress = "{$profile->street} {$houseNumber} {$profile->houseNumberAddition}";
        $dateOfBirth = $profile->dateOfBirth?->format('Y-m-d');
        $postcodeAndCity = "{$profile->postalCode} {$profile->city}";
        $quarterlyFee = ViewHelpers::formatEuro($repository->getQuarterlyFee($member));
        $memberSports = [];
        foreach ($repository->getSports($member) as $sport)
        {
            assert($sport->id !== null);
            $memberSports[] = $sport->id;
        }
        $graduations = [];
        foreach ($allSports as $sport)
        {
            $graduation = $repository->getHighestGraduation($member, $sport);
            if ($graduation !== null)
            {
                assert($graduation->id !== null);
                $graduations[] = $graduation->id;
            }
        }
        $hours = [];
        $locations = [];
        foreach ($repository->getHours($member) as $hour)
        {
            $dayName = ViewHelpers::getDutchWeekday($hour->day);
            $from = ViewHelpers::filterHm($hour->from);
            $until = ViewHelpers::filterHm($hour->until);
            $location = $hour->location;
            assert($location->id !== null);
            $hours[] = "{$dayName} {$from}-{$until} ({$hour->getSportName()}, {$location->getName()})";
            $locations[$location->id] = $location->id;
        }
        $locations = array_values($locations);

        return new self(
            (int)$member->id,
            $name,
            $streetAddress,
            $postcodeAndCity,
            $member->getEmail(),
            $member->getPhoneNumbers(),
            $hours,
            $member->iban,
            $quarterlyFee,
            (int)$member->isContestant,
            (int)$profile->canLogin(),
            (int)$member->isSenior(),
            $profile->gender ?? '',
            (int)$member->temporaryStop,
            $member->paymentMethod,
            (int)$member->paymentProblem,
            $dateOfBirth ?? '',
            $memberSports,
            $graduations,
            $locations,
        );
    }
}
