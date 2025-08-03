<?php
declare(strict_types=1);

namespace Cyndaron\RCTspace\RideExchange;

use Cyndaron\Error\ErrorPage;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\RCTspace\IPBoardConnector;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use PDO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use function is_array;
use function array_key_exists;
use function readfile;
use function assert;

final class RideExchangeController
{
    private const CATEGORY_MAP = [
        'Roller coaster',
        'Thrill ride',
        'Gentle ride',
        'Transport ride',
        'Water ride',
    ];

    public function __construct(
        private readonly IPBoardConnector $connector,
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function list(): Response
    {
        $query = 'SELECT * FROM for_ridex3_typename';
        $stmt = $this->connector->connection->prepare($query);
        $stmt->execute();
        $rideMap = [];
        $vehicleMap = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            assert(is_array($row));
            $rideMap[$row['Type_Num']] = $row['Category'];
            $vehicleMap[$row['Vehicle']] = $row['Category'];
        }
        unset($vehicleMap['NONE']);

        $stmt = $this->getTrackDesigns();

        $page = new Page();
        $page->title = 'Ride Exchange';
        $page->template = 'RCTspace/RideExchange/List';

        return $this->pageRenderer->renderResponse($page, [
            'categoryMap' => self::CATEGORY_MAP,
            'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'rideMap' => $rideMap,
            'vehicleMap' => $vehicleMap,
        ]);
    }

    #[RouteAttribute('download', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function download(QueryBits $queryBits): Response
    {
        $file_id = $queryBits->getInt(2);
        $record = null;

        $stmt = $this->getTrackDesigns();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            if (!is_array($row) || !array_key_exists('Id', $row))
            {
                break;
            }

            if ($row['Id'] == $file_id)
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

        $filename = $record['Disk_fname'];
        $mimetype = 'application/zip';

        return new StreamedResponse(function() use ($record)
        {
            $pathOnDisk = $this->connector->offurlPathRideExchange . $record['new_fname'] . '.zip';
            readfile($pathOnDisk);
        }, headers: [
            'Content-disposition' => 'attachment;filename="' . $filename . '"',
            'Content-Type' => $mimetype,
        ]);
    }

    private function getTrackDesigns(): \PDOStatement
    {
        $query = '
	SELECT frr.*, fm.name as uploader_name
	FROM for_ridex3_rides frr
	LEFT JOIN for_members fm ON fm.member_id = frr.Uploader
	ORDER BY Upload_date DESC';

        $stmt = $this->connector->connection->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
