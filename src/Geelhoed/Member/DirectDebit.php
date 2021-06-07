<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use function array_filter;
use function array_key_exists;
use function Safe\usort;
use function strcasecmp;
use function trim;

final class DirectDebit
{
    public string $iban = '';
    public string $ibanHolder = '';
    /** @var Member[] */
    public array $members = [];

    public function __construct(string $iban, string $ibanHolder, array $members = [])
    {
        $this->iban = $iban;
        $this->ibanHolder = $ibanHolder;
        $this->members = $members;
    }

    /**
     * @return DirectDebit[]
     */
    public static function load(): array
    {
        /** @var self[] $results */
        $results = [];
        $members = Member::fetchAll(["iban <> ''", "paymentMethod = 'incasso'"], [], 'ORDER BY iban');
        foreach ($members as $member)
        {
            // The IBAN holder might not be filled in on every member record.
            $iban = $member->iban;
            if (!array_key_exists($iban, $results))
            {
                $results[$iban] = new self($iban, $member->ibanHolder);
            }
            elseif ($results[$iban]->ibanHolder === '')
            {
                $results[$iban]->ibanHolder = $member->ibanHolder;
            }

            $results[$iban]->members[] = $member;
        }

        // If we still don't have a IBAN holder, fall back.
        foreach ($results as $result)
        {
            if ($result->ibanHolder === '')
            {
                $profile = $result->members[0]->getProfile();
                $result->ibanHolder = "$profile->tussenvoegsel $profile->lastName";
            }
            $result->ibanHolder = trim($result->ibanHolder);
        }
        $results = array_filter($results, static function(DirectDebit $result)
        {
            return $result->getTotalQuarterlyFee() !== 0.00;
        });
        usort($results, static function(DirectDebit $result1, DirectDebit $result2)
        {
            return strcasecmp($result1->ibanHolder, $result2->ibanHolder);
        });

        return $results;
    }

    public function getTotalQuarterlyFee(): float
    {
        $total = 0.0;
        foreach ($this->members as $member)
        {
            $total += $member->getQuarterlyFee();
        }

        return $total;
    }
}
