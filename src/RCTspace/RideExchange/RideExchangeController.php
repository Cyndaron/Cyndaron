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
use function html_entity_decode;
use function date;

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
        $trackDesigns = $this->getTrackDesigns();

        $page = new Page();
        $page->title = 'Ride Exchange';
        $page->template = 'RCTspace/RideExchange/List';

        return $this->pageRenderer->renderResponse($page, [
            'trackDesigns' => $trackDesigns,
        ]);
    }

    #[RouteAttribute('download', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function download(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);

        $trackDesigns = $this->getTrackDesigns();
        $trackDesign = $trackDesigns[$id] ?? null;

        if ($trackDesign === null)
        {
            $page = new ErrorPage('Error', 'File not found!', Response::HTTP_NOT_FOUND);
            return $this->pageRenderer->renderErrorResponse($page);
        }


        return new StreamedResponse(function() use ($trackDesign)
        {
            readfile($trackDesign->getPath());
        }, headers: [
            'Content-disposition' => 'attachment;filename="' . $trackDesign->realFilename . '"',
            'Content-Type' => $trackDesign->getMimeType(),
        ]);
    }

    /**
     * @return RideExchangeTrack[]
     */
    private function getTrackDesigns(): array
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

        $query = '
	SELECT frr.*, fm.name as uploader_name
	FROM for_ridex3_rides frr
	LEFT JOIN for_members fm ON fm.member_id = frr.Uploader
	ORDER BY Upload_date DESC';

        $stmt = $this->connector->connection->prepare($query);
        $stmt->execute();

        $ret = [];
        while ($row = $stmt->fetch())
        {
            if (!is_array($row) || !array_key_exists('Id', $row))
            {
                continue;
            }

            $id = $row['Id'];
            assert(!array_key_exists($id, $ret));

            $name = html_entity_decode($row['Ride_name'], encoding: 'UTF-8');
            $vehicle = $row['Vehicle_type'];
            if ($vehicle == 'NONE')
            {
                $vehicle = '';
            }
            $categoryId = $vehicleMap[$vehicle] ?? -1;
            if ($categoryId == -1)
            {
                $categoryId = $rideMap[$row['Ride_type']] ?? -1;
            }

            $category = (self::CATEGORY_MAP[$categoryId] ?? '');
            $submitter = html_entity_decode($row['uploader_name'] ?? '?', encoding: 'UTF-8');
            $submitDate = date('Y-m-d H:i:s', $row['Upload_date']);

            $ret[$id] = new RideExchangeTrack(
                id: $id,
                name: $name,
                vehicle: $vehicle,
                category: $category,
                submitter: $submitter,
                submitDate: $submitDate,
                zipLocation: $this->connector->offurlPathRideExchangeZip . $row['new_fname'] . '.zip',
                zipLocation2: $this->connector->offurlPathRideExchangeZipOld . $row['Disk_fname'] . '.zip',
                zipLocation3: $this->connector->offurlPathRideExchangeZipOlder . $row['Disk_fname'] . '.zip',
                uncompressedLocation: $this->connector->offurlPathRideExchangePlain . $row['new_fname'] . '.td6',
                uncompressedLocation2: $this->connector->offurlPathRideExchangePlain . $row['new_fname'] . '.td4',
                realFilename: $row['Disk_fname'],
            );
        }

        return $ret;
    }
}
