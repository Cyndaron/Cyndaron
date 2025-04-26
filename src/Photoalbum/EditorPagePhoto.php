<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\Request\QueryBits;

final class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    public const HAS_TITLE = false;
    public const TYPE = 'photo';
    public const SAVE_URL = '/editor/photo/%s';

    public string $template = '';

    public function __construct(
        private readonly QueryBits $queryBits,
        private readonly PhotoalbumCaptionRepository $photoalbumCaptionRepository,
    ) {
    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $this->model = $this->photoalbumCaptionRepository->fetchById($this->id);
            $this->content = $this->model?->caption ?? '';
        }

        $photoalbumId = $this->queryBits->getInt(4);
        $this->addTemplateVar('photoalbumId', $photoalbumId);
    }
}
