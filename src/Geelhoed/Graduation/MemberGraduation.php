<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Graduation;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Member\Member;

final class MemberGraduation extends Model
{
    public const TABLE = 'geelhoed_members_graduations';

    #[DatabaseField(dbName: 'memberId')]
    public Member $member;
    #[DatabaseField(dbName: 'graduationId')]
    public Graduation $graduation;
    #[DatabaseField]
    public string $date;
}
