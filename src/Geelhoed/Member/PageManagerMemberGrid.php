<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Util\FileCache;
use Cyndaron\Util\FileCacheLoadResult;
use function array_values;
use function usort;
use function strtolower;

final class PageManagerMemberGrid
{
    private readonly MemberRepository $memberRepository;
    private FileCache $cacheHandle;
    /** @var PageManagerMemberGridItem[] */
    private array $cache = [];
    private SportRepository $sportRepository;

    public function __construct(MemberRepository $memberRepository, SportRepository $sportRepository)
    {
        $this->memberRepository = $memberRepository;
        $this->sportRepository = $sportRepository;
        $this->cacheHandle = new FileCache('geelhoed-page-manager-member-grid', [PageManagerMemberGridItem::class]);
        if ($this->cacheHandle->load($this->cache) !== FileCacheLoadResult::OK)
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
        $sports = $this->sportRepository->fetchAll();
        foreach ($this->memberRepository->fetchAllAndSortByName() as $member)
        {
            $this->cache[] = PageManagerMemberGridItem::createFromMember($this->memberRepository, $member, $sports);
        }
        $this->saveCache();
    }

    public function deleteByMemberIds(int... $memberIds): void
    {
        $hasUpdated = false;
        foreach ($this->cache as $index => $gridItem)
        {
            foreach ($memberIds as $memberId)
            {
                if ($gridItem->id === $memberId)
                {
                    unset($this->cache[$index]);
                    $hasUpdated = true;
                }
            }
        }

        if ($hasUpdated)
        {
            // Needed to ensure the array is serialised to JSON properly
            $this->cache = array_values($this->cache);
            $this->saveCache();
        }
    }

    public function addItem(PageManagerMemberGridItem $item): void
    {
        $this->cache[] = $item;
        usort($this->cache, static function(PageManagerMemberGridItem $item1, PageManagerMemberGridItem $item2)
        {
            return strtolower($item1->name) <=> strtolower($item2->name);
        });
        $this->saveCache();
    }

    private function saveCache(): void
    {
        $this->cacheHandle->save($this->cache);
    }
}
