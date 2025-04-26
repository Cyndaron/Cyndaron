<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Util\FileCache;

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
        $sports = $this->sportRepository->fetchAll();
        foreach ($this->memberRepository->fetchAllAndSortByName() as $member)
        {
            $this->cache[] = PageManagerMemberGridItem::createFromMember($this->memberRepository, $member, $sports);
        }
        $this->cacheHandle->save($this->cache);
    }
}
