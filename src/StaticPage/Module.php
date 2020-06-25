<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\PageManager\PageManagerPage;
use Cyndaron\Template\Template;

class Module implements Datatypes, Routes, UrlProvider, Linkable
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'sub' => Datatype::fromArray([
                'singular' => 'Statische pagina',
                'plural' => 'Statische pagina\'s',
                'pageManagerTab' => self::class . '::pageManagerTab',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
            ]),
        ];
    }

    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'sub' => StaticPageController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        $model = StaticPageModel::loadFromDatabase((int)$linkParts[1]);
        return $model ? $model->name : null;
    }

    public function getList(): array
    {
        return DBConnection::doQueryAndFetchAll('SELECT CONCAT(\'/sub/\', id) AS link, CONCAT(\'Statische pag.: \', name) AS name FROM subs');
    }

    public static function pageManagerTab(): string
    {
        $template = new Template();
        $templateVars = [];

        /** @noinspection SqlResolve */
        $subs = DBConnection::doQueryAndFetchAll('SELECT id, name, "Zonder categorie" AS category FROM subs WHERE id NOT IN (SELECT id FROM sub_categories) UNION (SELECT s.id AS id, s.name AS name, c.name AS category FROM subs AS s,categories AS c WHERE c.id IN (SELECT categoryId FROM sub_categories WHERE id = s.id) ORDER BY category, name, id ASC);');
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
        return $template->render('StaticPage/PageManagerTab', $templateVars);
    }
}
