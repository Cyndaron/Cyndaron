<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Template\Template;

final class Module implements Datatypes, Routes, UrlProvider, Linkable
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
        return $model !== null ? $model->name : null;
    }

    public function getList(): array
    {
        return DBConnection::doQueryAndFetchAll('SELECT CONCAT(\'/sub/\', id) AS link, CONCAT(\'Statische pag.: \', name) AS name FROM subs') ?: [];
    }

    public static function pageManagerTab(): string
    {
        $template = new Template();
        $templateVars = [];

        $subs = DBConnection::doQueryAndFetchAll('SELECT s.id,s.name,c.name AS category,IF(sb.text IS NOT NULL, 1, 0) AS hasBackup FROM subs s LEFT JOIN sub_categories sc ON s.id = sc.id LEFT JOIN categories c ON sc.categoryId = c.id LEFT JOIN sub_backups sb ON s.id = sb.id ORDER BY category, name, id ASC') ?: [];
        $subsPerCategory = [];

        foreach ($subs as $sub)
        {
            $category = $sub['category'] ?? 'Zonder categorie';
            if (empty($subsPerCategory[$category]))
            {
                $subsPerCategory[$category] = [];
            }

            $subsPerCategory[$category][$sub['id']] = $sub;
        }

        $templateVars['subsPerCategory'] = $subsPerCategory;
        return $template->render('StaticPage/PageManagerTab', $templateVars);
    }
}
