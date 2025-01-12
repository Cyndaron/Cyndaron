<?php
namespace Cyndaron\User;

use Cyndaron\Page\Page;

final class UserManagerPage extends Page
{
    public const USER_LEVEL_DESCRIPTIONS = [
        'Niet ingelogd',
        'Normale gebruiker',
        'Gereserveerd',
        'Gereserveerd',
        'Beheerder',
    ];

    public function __construct()
    {
        $this->title = 'Gebruikersbeheer';
        $this->addScript('/src/User/js/UserManagerPage.js');
        $this->addTemplateVars([
            'users' => User::fetchAll([], [], 'ORDER BY username'),
            'userLevelDescriptions' => self::USER_LEVEL_DESCRIPTIONS,
        ]);
    }
}
