<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\Util\Link;
use Cyndaron\View\Template\TemplateRenderer;
use function array_map;

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
                'editorSave' => EditorSave::class,
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

    public function url(array $linkParts): string|null
    {
        $model = StaticPageModel::fetchById((int)$linkParts[1]);
        return $model !== null ? $model->name : null;
    }

    public function getList(Connection $connection): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = $connection->doQueryAndFetchAll('SELECT CONCAT(\'/sub/\', id) AS link, CONCAT(\'Statische pag.: \', name) AS name FROM subs');
        return array_map(static function(array $item)
        {
            return Link::fromArray($item);
        }, $list);
    }

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $templateVars = [];

        $subs = DBConnection::getPDO()->doQueryAndFetchAll('SELECT s.id,s.name,c.name AS category,IF(sb.text IS NOT NULL, 1, 0) AS hasBackup FROM subs s LEFT JOIN sub_categories sc ON s.id = sc.id LEFT JOIN categories c ON sc.categoryId = c.id LEFT JOIN sub_backups sb ON s.id = sb.id ORDER BY category, name, id ASC') ?: [];
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
        $templateVars['tokenDelete'] = $tokenHandler->get('sub', 'delete');
        $templateVars['tokenAddToMenu'] = $tokenHandler->get('sub', 'addtomenu');
        return $templateRenderer->render('StaticPage/PageManagerTab', $templateVars);
    }
}
