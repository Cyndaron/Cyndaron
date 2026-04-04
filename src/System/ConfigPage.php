<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\Category\CategoryRepository;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\SettingsRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class ConfigPage
{
    use SystemPageTrait;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly Translator $t,
        private readonly CategoryRepository $categoryRepository,
        private readonly SettingsRepository $sr,
    ) {

    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ADMIN)]
    #[RouteAttribute('config', RequestMethod::GET, UserLevel::ADMIN)]
    public function show(): Response
    {
        $page = new Page();
        $page->template = 'System/Config';
        $this->setCommonVariables($page, 'config');

        $page->addScript('/src/System/js/SystemPage.js');

        $page->templateVars['defaultCategory'] = $this->sr->get('defaultCategory');

        $frontPageIsJumbo = (bool)(int)$this->sr->get('frontPageIsJumbo');

        $formItems = [
            ['name' => 'siteName', 'description' => 'Naam website', 'type' => 'text', 'value' => $this->sr->get('siteName')],
            ['name' => 'organisation', 'description' => 'Organisatie', 'type' => 'text', 'value' => $this->sr->get(BuiltinSetting::ORGANISATION)],
            ['name' => 'shortCode', 'description' => 'Code (3 letters)', 'type' => 'text', 'value' => $this->sr->get(BuiltinSetting::SHORT_CODE)],
            ['name' => 'logo', 'description' => 'Websitelogo', 'type' => 'text', 'value' => $this->sr->get('logo')],
            ['name' => 'subTitle', 'description' => 'Ondertitel', 'type' => 'text', 'value' => $this->sr->get('subTitle')],
            ['name' => 'favicon', 'description' => 'Websitepictogram', 'type' => 'text', 'value' => $this->sr->get('favicon')],
            ['name' => 'backgroundColor', 'description' => 'Achtergrondkleur hele pagina', 'type' => 'color', 'value' => $this->sr->get('backgroundColor')],
            ['name' => 'menuColor', 'description' => 'Achtergrondkleur menu', 'type' => 'color', 'value' => $this->sr->get('menuColor')],
            ['name' => 'articleColor', 'description' => 'Achtergrondkleur artikel', 'type' => 'color', 'value' => $this->sr->get('articleColor')],
            ['name' => 'accentColor', 'description' => 'Accentkleur', 'type' => 'color', 'value' => $this->sr->get('accentColor')],
            ['name' => 'menuBackground', 'description' => 'Achtergrondafbeelding menu', 'type' => 'text', 'value' => $this->sr->get('menuBackground')],
            ['name' => 'frontPage', 'description' => 'Voorpagina', 'type' => 'text', 'value' => $this->sr->get('frontPage')],
            ['name' => 'frontPageIsJumbo', 'description' => 'Jumbotron op voorpagina', 'type' => 'checkbox', 'value' => 1, 'extraAttr' => $frontPageIsJumbo ? 'checked' : ''],
            ['name' => 'mail_logRecipient', 'description' => 'Mailadres bij fouten', 'type' => 'email', 'value' => $this->sr->get('mail_logRecipient')],
            ['name' => 'mollieApiKey', 'description' => 'API-key voor Mollie', 'type' => 'text', 'value' => $this->sr->get('mollieApiKey')],

        ];
        $page->templateVars['formItems'] = $formItems;

        $page->templateVars['categories'] = $this->categoryRepository->fetchAllAndSortByName();

        $menuTheme = $this->sr->get('menuTheme');
        $page->templateVars['lightMenu'] = ($menuTheme !== 'dark') ? 'selected' : '';
        $page->templateVars['darkMenu'] = ($menuTheme === 'dark') ? 'selected' : '';

        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('config', RequestMethod::POST, UserLevel::ADMIN)]
    public function routePost(RequestParameters $post, SettingsRepository $sr): Response
    {
        $sr->set('siteName', $post->getHTML('siteName'));
        $sr->set('organisation', $post->getHTML('organisation'));
        $sr->set('shortCode', $post->getHTML('shortCode'));
        $sr->set('logo', $post->getFilenameWithDirectory('logo'));
        $sr->set('subTitle', $post->getHTML('subTitle'));
        $sr->set('favicon', $post->getFilenameWithDirectory('favicon'));
        $sr->set('backgroundColor', $post->getColor('backgroundColor'));
        $sr->set('menuColor', $post->getColor('menuColor'));
        $sr->set('menuBackground', $post->getFilenameWithDirectory('menuBackground'));
        $sr->set('articleColor', $post->getColor('articleColor'));
        $sr->set('accentColor', $post->getColor('accentColor'));
        $sr->set('defaultCategory', (string)$post->getInt('defaultCategory'));
        $sr->set('menuTheme', $post->getSimpleString('menuTheme'));
        $sr->set('frontPage', $post->getUrl('frontPage'));
        $sr->set('frontPageIsJumbo', (string)(int)$post->getBool('frontPageIsJumbo'));
        $sr->set('mail_logRecipient', $post->getEmail('mail_logRecipient'));
        $sr->set('mollieApiKey', $post->getSimpleString('mollieApiKey'));
        $sr->buildCache();

        // Redirect to GET
        return new RedirectResponse('/system/config');
    }
}
