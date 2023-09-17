<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Connection;
use Cyndaron\MDB\MDBFile;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Util;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function implode;
use function array_chunk;

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
}
