<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\InternalLink;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\User\User;
use Cyndaron\View\Template\Template;
use function array_map;

final class Module implements Datatypes, Routes, UrlProvider, Linkable, WithTextPostProcessors
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'photo' => Datatype::fromArray([
                'singular' => 'Foto',
                'plural' => 'Foto\'s',
                'editorPage' => EditorPagePhoto::class,
                'editorSavePage' => EditorSavePagePhoto::class,
            ]),
            'photoalbum' => Datatype::fromArray([
                'singular' => 'Fotoalbum',
                'plural' => 'Fotoalbums',
                'pageManagerTab' => self::class . '::pageManagerTab',
                'pageManagerJS' => '/src/Photoalbum/js/PageManagerTab.js',
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
            'photoalbum' => PhotoalbumController::class,
        ];
    }

    public function url(array $linkParts): ?string
    {
        $album = Photoalbum::fetchById((int)$linkParts[1]);
        return $album !== null ? $album->name : null;
    }

    public function getList(): array
    {
        /** @var list<array{name: string, link: string}> $list */
        $list = DBConnection::getPDO()->doQueryAndFetchAll('SELECT CONCAT(\'/photoalbum/\', id) AS link, CONCAT(\'Fotoalbum: \', name) AS name FROM photoalbums');
        return array_map(static function (array $item)
        {
            return InternalLink::fromArray($item);
        }, $list);
    }

    public static function pageManagerTab(User $currentUser): string
    {
        $templateVars = [
            'photoalbums' => Photoalbum::fetchAll([], [], 'ORDER BY name'),
            'currentUser' => $currentUser,
        ];
        return (new Template())->render('Photoalbum/PageManagerTab', $templateVars);
    }

    public function getTextPostProcessors(): array
    {
        return [SliderRenderer::class];
    }
}
