<?php
namespace Cyndaron\User;

use Cyndaron\Page\Page;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;

final class Gallery extends Page
{
    private const FALLBACK_IMAGE = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __construct(UserRepository $userRepository, SettingsRepository $settingsRepository)
    {
        $members = $userRepository->fetchAll(['hideFromMemberList = 0'], [], 'ORDER BY lastname, tussenvoegsel, firstName');
        $title = $settingsRepository->get('userGalleryTitle') ?: 'Wie is wie';
        $this->title = $title;

        $this->addTemplateVars([
            'members' => $members,
            'fallbackImage' => self::FALLBACK_IMAGE,
        ]);
    }
}
