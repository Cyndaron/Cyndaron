<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Graduation\Graduation;
use Cyndaron\Geelhoed\Member\Member;

final class ContestMember extends Model
{
    public const TABLE = 'geelhoed_contests_members';

    #[DatabaseField(dbName: 'contestId')]
    public Contest $contest;
    #[DatabaseField(dbName: 'memberId')]
    public Member $member;
    #[DatabaseField(dbName: 'graduationId')]
    public Graduation $graduation;
    #[DatabaseField]
    public int $weight;
    #[DatabaseField]
    public string|null $molliePaymentId = null;
    #[DatabaseField]
    public bool $isPaid = false;
    #[DatabaseField]
    public string $comments = '';
}
