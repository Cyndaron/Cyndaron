<?php
declare (strict_types = 1);

namespace Cyndaron\PageManager;

use Cyndaron\Category\Category;
use Cyndaron\DBConnection;
use Cyndaron\Mailform\Mailform;
use Cyndaron\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Template\Template;

class PageManagerPage extends Page
{
    private static array $pageTypes = [];
    
    public function __construct(string $currentPage)
    {
        $this->addScript('/src/PageManager/PageManagerPage.js');
        parent::__construct('Paginaoverzicht');

        $pageTabs = [];
        foreach (static::$pageTypes as $pageType => $data)
        {
            $pageTabs[$pageType] = $data['name'];
        }

        $function = static::$pageTypes[$currentPage]['tabDraw'];
        $tabContents = $function();

        $this->addTemplateVars([
            'pageTabs' => $pageTabs,
            'currentPage' => $currentPage,
            'tabContents' => $tabContents,
        ]);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function showSubs(): string
    {
        $template = new Template();
        $templateVars = [];

        /** @noinspection SqlResolve */
        $subs = DBConnection::doQueryAndFetchAll('SELECT id, name, "Zonder categorie" AS category FROM subs WHERE categoryId NOT IN (SELECT id FROM categories) UNION (SELECT s.id AS id, s.name AS name, c.name AS category FROM subs AS s,categories AS c WHERE s.categoryId=c.id ORDER BY category, name, id ASC);');
        $subsPerCategory = [];

        foreach ($subs as $sub)
        {
            if (empty($subsPerCategory[$sub['category']]))
            {
                $subsPerCategory[$sub['category']] = [];
            }

            $subsPerCategory[$sub['category']][$sub['id']] = $sub['name'];
        }

        $templateVars['subsPerCategory'] = $subsPerCategory;
        return $template->render('PageManager/StaticPagesTab', $templateVars);
    }

    /** @noinspection PhpUnused */
    public static function showCategories(): string
    {
        $templateVars = ['categories' => Category::fetchAll([], [], 'ORDER BY name')];
        return (new Template())->render('PageManager/CategoriesTab', $templateVars);
    }

    /** @noinspection PhpUnused */
    public static function showPhotoAlbums(): string
    {
        $templateVars = ['photoalbums' => Photoalbum::fetchAll([], [], 'ORDER BY name')];
        return (new Template())->render('PageManager/PhotoalbumsTab', $templateVars);
    }

    /**
     * @noinspection PhpUnused
     */
    public static function showFriendlyURLs(): string
    {
        $templateVars = ['friendlyUrls' => DBConnection::doQueryAndFetchAll('SELECT * FROM friendlyurls ORDER BY name ASC;')];
        return (new Template())->render('PageManager/FriendlyUrlsTab', $templateVars);
    }

    /**
     * @noinspection PhpUnused
     */
    public static function showMailforms(): string
    {
        $templateVars = ['mailforms' => Mailform::fetchAll([], [], 'ORDER BY name')];
        return (new Template())->render('PageManager/MailformsTab', $templateVars);
    }

    /**
     * Adds a tab definition to the page manager.
     *
     * @param array $pageType
     */
    public static function addPageType(array $pageType): void
    {
        static::$pageTypes = array_merge(static::$pageTypes, $pageType);
    }
}