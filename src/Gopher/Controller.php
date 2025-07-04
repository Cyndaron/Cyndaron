<?php
declare(strict_types=1);

namespace Cyndaron\Gopher;

use Cyndaron\Category\Category;
use Cyndaron\Category\CategoryRepository;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Gopher\Response\FilestreamResponse;
use Cyndaron\Gopher\Response\ListingResponse;
use Cyndaron\Gopher\Response\PlainTextResponse;
use Cyndaron\Gopher\Response\ResponseInterface;
use Cyndaron\Menu\MenuItem;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Photoalbum\PhotoRepository;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\ViewHelpers;
use function array_key_exists;
use function array_map;
use function assert;
use function explode;
use function html_entity_decode;
use function ltrim;
use function str_ends_with;
use function str_repeat;
use function str_starts_with;
use function strip_tags;
use function strlen;
use function strtr;
use function substr;
use function trim;

final class Controller
{
    public function __construct(
        private readonly MenuEntryFactory $menuEntryFactory,
        private readonly UrlService       $urlService,
        private readonly GenericRepository $genericRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    public function processQuery(string $query): ResponseInterface
    {
        if (str_starts_with($query, 'sub/'))
        {
            $id = (int)substr($query, 4);
            $page = $this->genericRepository->fetchById(StaticPageModel::class, $id);
            if ($page !== null)
            {
                return $this->serveStaticPage($page);
            }
        }
        elseif (str_starts_with($query, 'category/'))
        {
            $id = (int)substr($query, 9);
            $page = $this->categoryRepository->fetchById($id);
            if ($page !== null)
            {
                return $this->serveCategory($page);
            }
        }
        elseif (str_starts_with($query, 'photoalbum/'))
        {
            $id = (int)substr($query, 11);
            $page = $this->genericRepository->fetchById(Photoalbum::class, $id);
            if ($page !== null)
            {
                return $this->servePhotoalbum($page);
            }
        }
        elseif (str_starts_with($query, 'photo/'))
        {
            $info = substr($query, 6);
            [$albumId, $filename] = explode('/', $info);
            return $this->servePhoto((int)$albumId, $filename);
        }

        return $this->listIndex();
    }

    public function listIndex(): ListingResponse
    {
        $websiteTitle = Setting::get('siteName');
        $subtitle = Setting::get('subTitle');
        $separator = str_repeat('-', strlen($subtitle));
        $entries = [
            $this->menuEntryFactory->createTitleEntry($websiteTitle),
            $this->menuEntryFactory->createInformationEntry($subtitle),
            $this->menuEntryFactory->createInformationEntry($separator),
            $this->menuEntryFactory->createInformationEntry(''),
        ];

        $menu = $this->genericRepository->fetchAll(MenuItem::class);
        foreach ($menu as $menuItem)
        {
            $url = $menuItem->getLink()->__toString();
            // We are already on the front page, so skip it.
            if ($url === '/')
            {
                continue;
            }
            if (str_starts_with($url, 'http'))
            {
                $entries[] = $this->menuEntryFactory->createHtmlLinkEntry($url, $menuItem->alias);
                continue;
            }
            $obj = new Url($url);
            $unfriendly = $this->urlService->toUnfriendly($obj)->__toString();
            $unfriendly = ltrim($unfriendly, '/');
            $parts = explode('/', $unfriendly);
            switch ($parts[0] ?? '')
            {
                case 'category':
                    $id = (int)$parts[1];
                    $category = $this->categoryRepository->fetchById($id);
                    assert($category !== null);
                    $entries[] = $this->menuEntryFactory->createDirectoryEntry("/category/$id", $category->name);
                    break;
                case 'sub':
                    $id = (int)$parts[1];
                    $sub = $this->genericRepository->fetchById(StaticPageModel::class, $id);
                    assert($sub !== null);
                    $entries[] = $this->menuEntryFactory->createHtmlFileEntry("/sub/$id", $sub->name);
                    break;
                case 'photoalbum':
                    $id = (int)$parts[1];
                    $photoalbum = $this->genericRepository->fetchById(Photoalbum::class, $id);
                    assert($photoalbum !== null);
                    $entries[] = $this->menuEntryFactory->createDirectoryEntry("/photoalbum/$id", $photoalbum->name);
                    break;
                case 'richlink':

            }

        }

        return new ListingResponse(...$entries);
    }

    public function serveStaticPage(StaticPageModel $page): PlainTextResponse
    {
        return new PlainTextResponse($page->getText());
    }

    public function serveCategory(Category $category): ListingResponse
    {
        $entries = [
            $this->menuEntryFactory->createTitleEntry($category->name),
            $this->menuEntryFactory->createInformationEntry(''),
        ];

        $lines = $this->wrapText($this->stripHtml($category->description));
        foreach ($lines as $line)
        {
            $entries[] = $this->menuEntryFactory->createInformationEntry($line);
        }
        $entries[] = $this->menuEntryFactory->createInformationEntry('');

        foreach ($this->categoryRepository->getUnderlyingPages($category) as $page)
        {
            if ($page instanceof StaticPageModel)
            {
                $entries[] = $this->menuEntryFactory->createHtmlFileEntry("/sub/{$page->id}", $page->name);
            }
            elseif ($page instanceof Category)
            {
                $entries[] = $this->menuEntryFactory->createDirectoryEntry("/category/{$page->id}", $page->name);
            }
            elseif ($page instanceof Photoalbum)
            {
                $entries[] = $this->menuEntryFactory->createDirectoryEntry("/photoalbum/{$page->id}", $page->name);
            }
            elseif ($page instanceof \Cyndaron\RichLink\RichLink)
            {
                $entries[] = $this->menuEntryFactory->createHtmlLinkEntry($page->url, $page->name);
            }
        }

        return new ListingResponse(...$entries);
    }

    public function servePhotoalbum(Photoalbum $album): ListingResponse
    {
        $entries = [];

        $lines = $this->wrapText($this->stripHtml($album->notes));
        foreach ($lines as $line)
        {
            $entries[] = $this->menuEntryFactory->createInformationEntry($line);
        }
        $entries[] = $this->menuEntryFactory->createInformationEntry('');

        $photos = $this->photoRepository->fetchAllByAlbum($album);
        foreach ($photos as $photo)
        {
            $title = $photo->caption->caption ?? $photo->filename;
            if ($photo->caption?->caption)
            {
                $title = html_entity_decode(ViewHelpers::wordlimit(trim(strip_tags($title)), 30));
            }

            $link = "/photo/{$album->id}/{$photo->filename}";
            $entries[] = $this->menuEntryFactory->createEntry(EntryType::ImageFile, $title, $link);
        }

        return new ListingResponse(...$entries);
    }

    public function servePhoto(int $albumId, string $filename): FilestreamResponse
    {
        $filename = Photoalbum::getPhotoalbumsDir() . "/{$albumId}/originals/{$filename}";
        return new FilestreamResponse($filename);
    }

    private function stripHtml(string $input): string
    {
        $output = strtr($input, [
            "\r" => '',
            "\n" => ' ',
            "<br>" => "\n",
            "<br/>" => "\n",
            "<br />" => "\n",
            "</p>" => "</p>\n\n",
        ]);

        return trim(html_entity_decode(strip_tags($output)));
    }

    /**
     * @return string[]
     */
    private function wrapText(string $input, int $limit = 70): array
    {
        $lines = [];
        $lineIndex = 0;
        $words = explode(' ', $input);

        foreach ($words as $word)
        {
            if (!array_key_exists($lineIndex, $lines))
            {
                $lines[$lineIndex] = '';
            }

            $lineFeed = false;

            if (str_starts_with($word, "\n"))
            {
                $lineIndex++;
                if (!array_key_exists($lineIndex, $lines))
                {
                    $lines[$lineIndex] = '';
                }
            }
            if (str_ends_with($word, "\n"))
            {
                $lineFeed = true;
            }

            $word = trim($word);

            $proposedLine = $lines[$lineIndex] . " {$word}";

            if (strlen($proposedLine) > $limit)
            {
                $lineIndex++;
                $proposedLine = $word;
            }

            $lines[$lineIndex] = $proposedLine;

            if ($lineFeed)
            {
                $lineIndex++;
            }
        }

        return array_map(trim(...), $lines);
    }
}
