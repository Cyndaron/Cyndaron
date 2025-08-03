<?php
declare(strict_types=1);

namespace Cyndaron\RCTspace\Downloads;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\RCTspace\IPBoardConnector;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function readfile;
use function is_array;
use function array_key_exists;

final class DownloadsController
{
    public function __construct(
        private readonly IPBoardConnector $connector,
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function list(): Response
    {
        $stmt = $this->getDownloads();

        $page = new Page();
        $page->title = 'Downloads';
        $page->template = 'RCTspace/Downloads/List';

        return $this->pageRenderer->renderResponse($page, ['rows' => $stmt->fetchAll()]);
    }

    #[RouteAttribute('download', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function download(QueryBits $queryBits): Response
    {
        $file_id = $queryBits->getInt(2);
        $record = null;

        $stmt = $this->getDownloads();
        while ($row = $stmt->fetch())
        {
            if (!is_array($row) || !array_key_exists('file_id', $row))
            {
                break;
            }

            if ($row['file_id'] == $file_id)
            {
                $record = $row;
                break;
            }
        }

        if ($record === null)
        {
            $page = new ErrorPage('Error', 'File not found!', Response::HTTP_NOT_FOUND);
            return $this->pageRenderer->renderErrorResponse($page);
        }

        $filename = $record['record_realname'];
        $mimetype = $record['mimetype'];

        return new StreamedResponse(function() use ($record)
        {
            $pathOnDisk = $this->connector->offurlPath . $record['record_location'];
            readfile($pathOnDisk);
        }, headers: [
            'Content-disposition' => 'attachment;filename="' . $filename . '"',
            'Content-Type' => $mimetype,
        ]);
    }

    private function getDownloads(): \PDOStatement
    {
        $query = '
	SELECT *,dc.cname AS cname ,fm.name AS submitter, dfr.record_location as record_location, dfr.record_realname as record_realname, dm.mime_mimetype as mimetype
	FROM for_downloads_files df
	LEFT JOIN for_downloads_categories dc ON df.file_cat = dc.cid
	LEFT JOIN for_members fm ON df.file_submitter = fm.member_id
	LEFT JOIN for_downloads_files_records dfr ON df.file_id = dfr.record_file_id
	    AND dfr.record_id =
	    (
	       SELECT MAX(record_id)
	       FROM for_downloads_files_records dfr2
	       WHERE dfr2.record_file_id = dfr.record_file_id
	       AND dfr2.record_type = \'upload\'
	    )
  	LEFT JOIN for_downloads_mime dm ON dfr.record_mime = dm.mime_id
	ORDER BY file_submitted';

        $stmt = $this->connector->connection->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
