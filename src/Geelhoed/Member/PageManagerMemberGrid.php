<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Util\FileCache;

final class PageManagerMemberGrid
{
    private FileCache $cacheHandle;
    /** @var PageManagerMemberGridItem[] */
    private array $cache = [];

    public function __construct()
    {
        $this->cacheHandle = new FileCache('geelhoed-page-manager-member-grid', [PageManagerMemberGridItem::class]);
        if (!$this->cacheHandle->load($this->cache))
        {
            $this->rebuild();
        }
    }

    /**
     * @return PageManagerMemberGridItem[]
     */
    public function get(): array
    {
        return $this->cache;
    }

    public function rebuild(): void
    {
        $this->cache = [];
        foreach (Member::fetchAllAndSortByName() as $member)
        {
            $this->cache[] = PageManagerMemberGridItem::createFromMember($member);
        }
        $this->cacheHandle->save($this->cache);
    }
}
