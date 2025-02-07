<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\Url\Url;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
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
                class: Photoalbum::class,
                modelToUrl: function(Photoalbum $photoalbum)
                { return new Url("/photoalbum/{$photoalbum->id}"); },
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

    public function nameFromUrl(GenericRepository $genericRepository, array $linkParts): string|null
    {
        $album = $genericRepository->fetchById(Photoalbum::class, (int)$linkParts[1]);
        return $album?->name;
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

    public static function pageManagerTab(User $currentUser, TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, PhotoalbumRepository $photoalbumRepository, UserRepository $userRepository): string
    {
        $templateVars = [
            'canEdit' => $userRepository->userHasRight($currentUser, Photoalbum::RIGHT_EDIT),
            'photoalbums' => $photoalbumRepository->fetchAllAndSortByName(),
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
