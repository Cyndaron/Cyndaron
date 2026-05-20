<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\Module\Routes;
use Cyndaron\Module\Setting;
use Cyndaron\Module\Settings;
use Cyndaron\Module\SettingType;

final class Module implements Routes, Settings
{
    public function routes(): array
    {
        return [
            'system' => [
                AboutPage::class,
                ConfigPage::class,
                ChecksPage::class,
                PHPInfoPage::class,
            ]
        ];
    }

    public function settings(): array
    {
        return [
            new Setting('siteName', SettingType::HTML, 'Naam website'),
            new Setting('organisation', SettingType::HTML, 'Organisatie'),
            new Setting('shortCode', SettingType::HTML, 'Code (3 letters)'),
            new Setting('logo', SettingType::FILENAME_WITH_DIRECTORY, 'Websitelogo'),
            new Setting('subTitle', SettingType::HTML, 'Ondertitel'),
            new Setting('favicon', SettingType::FILENAME_WITH_DIRECTORY, 'Websitepictogram'),
            new Setting('backgroundColor', SettingType::COLOR, 'Achtergrondkleur hele pagina'),
            new Setting('menuColor', SettingType::COLOR, 'Achtergrondkleur menu'),
            new Setting('menuBackground', SettingType::FILENAME_WITH_DIRECTORY, 'Achtergrondafbeelding menu'),
            new Setting('articleColor', SettingType::COLOR, 'Achtergrondkleur artikel'),
            new Setting('accentColor', SettingType::COLOR, 'Accentkleur'),
            new Setting('defaultCategory', SettingType::INTEGER, 'Standaardcategorie'),
            new Setting('menuTheme', SettingType::SIMPLE_STRING, 'Menuthema'),
            new Setting('frontPage', SettingType::URL, 'Voorpagina'),
            new Setting('frontPageIsJumbo', SettingType::CHECKBOX, 'Jumbotron op voorpagina'),
            new Setting('mail_logRecipient', SettingType::EMAIL, 'Mailadres bij fouten'),
            new Setting('mollieApiKey', SettingType::SIMPLE_STRING, 'API-key voor Mollie'),
        ];
    }
}
