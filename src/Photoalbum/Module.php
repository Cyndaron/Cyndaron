<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\Connection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use Cyndaron\Util\Link;
use Cyndaron\View\Template\TemplateRenderer;
use function array_map;

final class Module implements Datatypes, Routes, UrlProvider, Linkable, WithTextPostProcessors
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'photo' => new Datatype(
                singular: 'Foto',
                plural: 'Foto\'s',
                editorPage: EditorPagePhoto::class,
                editorSave: EditorSavePhoto::class,
            ),
            'photoalbum' => new Datatype(
                singular: 'Fotoalbum',
                plural: 'Fotoalbums',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: self::pageManagerTab(...),
                pageManagerJS: '/src/Photoalbum/js/PageManagerTab.js',
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'photoalbum' => PhotoalbumController::class,
        ];
    }

    public function url(array $linkParts): string|null
    {
        $album = Photoalbum::fetchById((int)$linkParts[1]);
        return $album !== null ? $album->name : null;
    }

    public function getList(Connection $connection): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = $connection->doQueryAndFetchAll('SELECT CONCAT(\'/photoalbum/\', id) AS link, CONCAT(\'Fotoalbum: \', name) AS name FROM photoalbums');
        return array_map(static function(array $item)
        {
            return Link::fromArray($item);
        }, $list);
    }

    public static function pageManagerTab(User $currentUser, TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $templateVars = [
            'photoalbums' => Photoalbum::fetchAllAndSortByName(),
            'currentUser' => $currentUser,
            'tokenAdd' => $tokenHandler->get('photoalbum', 'add'),
            'tokenDelete' => $tokenHandler->get('photoalbum', 'delete'),
            'tokenAddToMenu' => $tokenHandler->get('photoalbum', 'addtomenu'),
        ];
        return $templateRenderer->render('Photoalbum/PageManagerTab', $templateVars);
    }

    public function getTextPostProcessors(): array
    {
        return [SliderRenderer::class];
    }
}
