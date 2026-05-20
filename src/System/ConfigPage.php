<?php
declare(strict_types=1);

namespace Cyndaron\System;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Category\CategoryRepository;
use Cyndaron\Module\Setting;
use Cyndaron\Module\SettingType;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\SettingsRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function in_array;

final class ConfigPage
{
    use SystemPageTrait;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly Translator $t,
        private readonly CategoryRepository $categoryRepository,
        private readonly SettingsRepository $sr,
        private readonly ModuleRegistry $registry,
    ) {

    }

    private function getHTMLInputType(SettingType $settingType): string
    {
        return match ($settingType)
        {
            SettingType::COLOR => 'color',
            SettingType::CHECKBOX => 'checkbox',
            SettingType::EMAIL => 'email',
            default => 'text',
        };
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

        $formItems = [];
        foreach ($this->registry->settings as $setting)
        {
            if (in_array($setting->code, ['menuTheme', 'defaultCategory'], true))
            {
                continue;
            }

            $formItem = [
                'name' => $setting->code,
                'description' => $setting->description,
                'type' => $this->getHTMLInputType($setting->type),
                'value' => $this->sr->get($setting->code),
            ];
            if ($setting->type === SettingType::CHECKBOX)
            {
                $enabled = (bool)(int)$formItem['value'];
                $formItem['value'] = 1;
                $formItem['extraAttr'] = $enabled ? 'checked' : '';
            }

            $formItems[] = $formItem;
        }

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
        foreach ($this->registry->settings as $setting)
        {
            $filteredValue = match($setting->type)
            {
                SettingType::COLOR => $post->getColor($setting->code),
                SettingType::CHECKBOX => (string)(int)$post->getBool($setting->code),
                SettingType::EMAIL => $post->getEmail($setting->code),
                SettingType::FILENAME_WITH_DIRECTORY => $post->getFilenameWithDirectory($setting->code),
                SettingType::HTML => $post->getHTML($setting->code),
                SettingType::INTEGER => (string)$post->getInt($setting->code),
                SettingType::SIMPLE_STRING => $post->getSimpleString($setting->code),
                SettingType::URL => $post->getUrl($setting->code),
            };
            $sr->set($setting->code, $filteredValue);
        }

        $sr->buildCache();

        // Redirect to GET
        return new RedirectResponse('/system/config');
    }
}
