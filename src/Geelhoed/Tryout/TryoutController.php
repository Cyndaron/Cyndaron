<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\Category\Category;
use Cyndaron\Category\ViewMode;
use Cyndaron\DBAL\Connection;
use Cyndaron\FriendlyUrl\FriendlyUrl;
use Cyndaron\Mail\Mail;
use Cyndaron\MDB\MDBFile;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Throwable;
use function assert;
use function implode;
use function array_chunk;
use const PHP_EOL;
use function explode;

class TryoutController extends Controller
{
    public const RIGHT_UPLOAD = 'geelhoed_tryout_upload';

    private const BATCH_SIZE = 250;
    private const QUERY = 'REPLACE INTO geelhoed_tryout_points(`id`, `code`, `datetime`, `points`) VALUES ';

    protected array $getRoutes = [
        'scores' => ['function' => 'scores', 'level' => UserLevel::ANONYMOUS],
        'update' => ['function' => 'updateGet', 'level' => UserLevel::ADMIN, 'right' => self::RIGHT_UPLOAD],
    ];
    protected array $postRoutes = [
        'update' => ['function' => 'updatePost', 'level' => UserLevel::ADMIN, 'right' => self::RIGHT_UPLOAD],
    ];
    protected array $apiPostRoutes = [
        'create-photoalbums' => ['function' => 'createPhotoalbums', 'level' => UserLevel::ADMIN],
    ];

    public function scores(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id === 0)
        {
            return $this->scoresForm();
        }

        $page = new ScoresPage($id);
        return new Response($page->render());
    }

    private function scoresForm(): Response
    {
        $page = new ScoresFormPage();
        return new Response($page->render());
    }

    public function updateGet(): Response
    {
        $page = new UpdateFormPage();
        return new Response($page->render());
    }

    public function updatePost(Request $request, Connection $db): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('datatot');
        $file = new MDBFile($file->getPathname());
        $this->updateFromFile($file, $db);
        $page = new UpdatePage();
        return new Response($page->render());
    }

    private function updateFromFile(MDBFile $file, Connection $db): void
    {
        $punten = $file->getTableData('punten');

        $batches = array_chunk($punten, self::BATCH_SIZE);
        foreach ($batches as $batch)
        {
            $placeholders = [];
            $vars = [];

            foreach ($batch as $punt)
            {
                try
                {
                    $date = DateTimeImmutable::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $punt['datum']);
                    $time = DateTimeImmutable::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $punt['tijd']);
                    $datetime = $date->setTime((int)$time->format('H'), (int)$time->format('i'), (int)$time->format('s'));
                }
                catch(Throwable)
                {
                    $datetime = null;
                }

                $placeholders[] = '(?, ?, ?, ?)';

                $vars[] = (int)$punt['Id'];
                $vars[] = (int)$punt['code'];
                $vars[] = $datetime?->format(Util::SQL_DATE_TIME_FORMAT);
                $vars[] = (int)$punt['punten'];
            }

            $db->executeQuery(self::QUERY . implode(',', $placeholders), $vars);
        }
    }

    public function createPhotoalbums(QueryBits $queryBits): JsonResponse
    {
        $tryoutId = $queryBits->getInt(2);
        $tryout = Tryout::fetchById($tryoutId);
        if ($tryout === null)
        {
            return new JsonResponse(['error' => 'Tryout bestaat niet!'], Response::HTTP_NOT_FOUND);
        }
        if ($tryout->photoalbumLink !== '')
        {
            return new JsonResponse(['error' => 'Tryout heeft al fotoalbums!'], Response::HTTP_NOT_FOUND);
        }

        $date = ViewHelpers::filterDutchDate($tryout->start);
        $dateSlug = Util::slug($date);
        $albumName = "Foto’s Tryout-toernooi {$date}";
        $slug = "fotos-tryout-{$dateSlug}";

        $category = new Category();
        $category->name = $albumName;
        $category->blurb = $date;
        $category->viewMode = ViewMode::Blog;
        $category->save();
        $categoryId = $category->id;
        assert($categoryId !== null);
        $friendlyUrl = new FriendlyUrl();
        $friendlyUrl->name = $slug;
        $friendlyUrl->target = '/category/' . $categoryId;
        $friendlyUrl->save();

        $tryout->photoalbumLink = "/{$slug}";
        $tryout->save();

        $roundUrls = [];
        for ($round = 1; $round <= 3; $round++)
        {
            $roundUrl = "{$slug}-ronde-{$round}";
            $album = new Photoalbum();
            $album->name = "{$albumName}, ronde {$round}";
            $album->blurb = "Ronde {$round}";
            $album->save();
            $albumId = $album->id;
            assert($albumId !== null);
            $album->addCategory($category, $round);
            $friendlyUrl = new FriendlyUrl();
            $friendlyUrl->name = $roundUrl;
            $friendlyUrl->target = '/photoalbum/' . $albumId;
            $friendlyUrl->save();

            $roundUrls[] = $roundUrl;
        }

        $to = Setting::get('tryout_photoRecipients');
        /** @var Address[] $toAddresses */
        $toAddresses = [];
        if ($to !== '')
        {
            foreach (explode(',', $to) as $toAddress)
            {
                $toAddresses[] = new Address($toAddress);
            }
        }

        foreach ($toAddresses as $toAddress)
        {
            $plainText = "Er zijn fotopagina’s aangemaakt voor het Tryouttoernooi van " . $date . ':' . PHP_EOL. PHP_EOL;
            foreach ($roundUrls as $roundUrl)
            {
                $plainText .= 'https://' . Util::getDomain() . $roundUrl . PHP_EOL;
            }

            $mail = \Cyndaron\Util\Mail::createMailWithDefaults($toAddress, 'Fotoalbums aangemaakt', $plainText);
            $mail->send();
        }

        return new JsonResponse(['id' => $tryoutId]);
    }
}
