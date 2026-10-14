<?php
declare(strict_types=1);

namespace Cyndaron\Editor;

use Cyndaron\DBAL\Model;
use Cyndaron\FriendlyUrl\FriendlyUrl;
use Cyndaron\FriendlyUrl\FriendlyUrlRepository;
use Cyndaron\Menu\MenuItemRepository;
use Cyndaron\Url\UrlService;

final class FriendlyUrlHelper
{
    public function __construct(
        private readonly MenuItemRepository $menuItemRepository,
        private readonly UrlService $urlService,
        private readonly FriendlyUrlRepository $repository,
    ) {
    }

    private function deleteOldFriendlyUrl(FriendlyUrl|null $oldFriendlyUrl): void
    {
        if ($oldFriendlyUrl === null)
        {
            return;
        }

        $this->repository->delete($oldFriendlyUrl);
    }

    private function updateReferencesToOldFriendlyUrl(FriendlyUrl|null $oldFriendlyUrl, string $newFriendlyUrl): void
    {
        if ($oldFriendlyUrl === null)
        {
            return;
        }

        // Menu items may refer to the old friendly URL. If so, update them.
        $menuItems = $this->menuItemRepository->fetchByLink($oldFriendlyUrl->name);
        foreach ($menuItems as $menuItem)
        {
            $menuItem->link = $newFriendlyUrl;
            $this->menuItemRepository->save($menuItem);
        }
    }

    public function updateFriendlyUrl(Model $model, string $newFriendlyUrl): void
    {
        if (!$this->urlService->modelSupportsFriendlyUrls($model))
        {
            return;
        }

        $unfriendlyUrl = $this->urlService->getUrlForModel($model);
        $oldFriendlyUrl = $this->repository->fetchByTarget($unfriendlyUrl);

        if ($newFriendlyUrl === '')
        {
            $this->updateReferencesToOldFriendlyUrl($oldFriendlyUrl, (string)$unfriendlyUrl);
            $this->deleteOldFriendlyUrl($oldFriendlyUrl);
            return;
        }

        // Old and new name are the same, no need to update or remove anything.
        if ($oldFriendlyUrl?->name === $newFriendlyUrl)
        {
            return;
        }

        // TODO: check if another page already uses this URL: if so, either error out or take over that friendly URL.
        $this->deleteOldFriendlyUrl($oldFriendlyUrl);
        $this->urlService->createFriendlyUrl($unfriendlyUrl, $newFriendlyUrl);
        $this->updateReferencesToOldFriendlyUrl($oldFriendlyUrl, $newFriendlyUrl);
    }
}
