<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class MemberGraduation extends Model
{
    public const TABLE = 'geelhoed_members_graduations';

    #[DatabaseField]
    public int $memberId;
    #[DatabaseField(dbName: 'graduationId')]
    public Graduation $graduation;
    #[DatabaseField]
    public string $date;
}
