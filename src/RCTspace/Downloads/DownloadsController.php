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
use function assert;
use function date;
use function html_entity_decode;

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
        $page = new Page();
        $page->title = 'Downloads';
        $page->template = 'RCTspace/Downloads/List';

        return $this->pageRenderer->renderResponse($page, ['downloads' => $this->getDownloads()]);
    }

    #[RouteAttribute('download', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function download(QueryBits $queryBits): Response
    {
        $fileId = $queryBits->getInt(2);

        $downloads = $this->getDownloads();
        $download = $downloads[$fileId] ?? null;
        if ($download === null)
        {
            $page = new ErrorPage('Error', 'File not found!', Response::HTTP_NOT_FOUND);
            return $this->pageRenderer->renderErrorResponse($page);
        }

        return new StreamedResponse(function() use ($download)
        {
            readfile($download->getPath());
        }, headers: [
            'Content-disposition' => 'attachment;filename="' . $download->realFilename . '"',
            'Content-Type' => $download->mimeType,
        ]);
    }

    /**
     * @return Download[]
     */
    private function getDownloads(): array
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
	       AND dfr2.record_backup = 0
	    )
  	LEFT JOIN for_downloads_mime dm ON dfr.record_mime = dm.mime_id
	ORDER BY file_submitted';

        $stmt = $this->connector->connection->prepare($query);
        $stmt->execute();

        $ret = [];
        while ($row = $stmt->fetch())
        {
            if (!is_array($row) || !array_key_exists('file_id', $row))
            {
                continue;
            }

            $id = $row['file_id'];
            assert(!array_key_exists($id, $ret));

            $name = html_entity_decode($row['file_name'] ?? '?', encoding: 'UTF-8');
            $category = html_entity_decode($row['cname'] ?? '?', encoding: 'UTF-8');
            $submitter = html_entity_decode($row['submitter'] ?? '?', encoding: 'UTF-8');
            $submitDate = date('Y-m-d H:i:s', $row['file_submitted']);
            if ($row['file_version'])
            {
                $name .= ' ' . html_entity_decode($row['file_version'], encoding: 'UTF-8');
            }

            $ret[$id] = new Download(
                id: $id,
                name: $name,
                category: $category,
                submitter: $submitter,
                submitDate: $submitDate,
                offUrlRoot: $this->connector->offurlPath,
                relativeLocation: $row['record_location'],
                realFilename: $row['record_realname'] ?: $row['record_location'],
                mimeType: $row['mimetype']
            );
        }

        return $ret;
    }
}
