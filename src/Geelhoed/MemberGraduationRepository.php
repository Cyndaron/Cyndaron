<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Util\FileCache;
use function array_key_exists;

/**
 * @implements RepositoryInterface<MemberGraduation>
 */
final class MemberGraduationRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = MemberGraduation::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    public function save(Model $model): void
    {
        $this->genericRepository->save($model);
        $this->rebuildByMemberCache();
    }

    /** @var array<int, MemberGraduation[]> */
    private static array $byMemberCache = [];
    private static FileCache $byMemberCacheHandle;

    public function rebuildByMemberCache(): void
    {
        if (empty(self::$byMemberCacheHandle))
        {
            self::$byMemberCacheHandle = new FileCache('member_graduation_by_member', [self::class]);
            self::$byMemberCacheHandle->load(self::$byMemberCache);
        }

        self::$byMemberCache = [];

        $memberGraduations = self::fetchAll([], [], 'ORDER BY date');
        foreach ($memberGraduations as $memberGraduation)
        {
            $memberId = $memberGraduation->memberId;
            if (!array_key_exists($memberId, self::$byMemberCache))
            {
                self::$byMemberCache[$memberId] = [];
            }

            self::$byMemberCache[$memberId][] = $memberGraduation;
        }

        self::$byMemberCacheHandle->save(self::$byMemberCache);
    }

    /**
     * @param Member $member
     * @return MemberGraduation[]
     */
    public function fetchAllByMember(Member $member): array
    {
        if (empty(self::$byMemberCache))
        {
            $this->rebuildByMemberCache();
        }

        return self::$byMemberCache[$member->id] ?? [];
    }
}
