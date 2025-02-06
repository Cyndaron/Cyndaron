<?php
namespace Cyndaron\Photoalbum;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'photoalbum';
    public const HAS_CATEGORY = true;
    public const SAVE_URL = '/editor/photoalbum/%s';

    public string $template = '';

    public function __construct(
        private readonly PhotoalbumRepository $photoalbumRepository
    ) {

    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $photoalbum = $this->photoalbumRepository->fetchById($this->id);
            if ($photoalbum !== null)
            {
                $this->model = $photoalbum;
                $this->content = $photoalbum->notes;
                $this->contentTitle = $photoalbum->name;
                $this->linkedCategories = $this->photoalbumRepository->getLinkedCategories($photoalbum);
            }
        }
        $this->templateVars['viewModeOptions'] = Photoalbum::VIEWMODE_DESCRIPTIONS;
    }
}
