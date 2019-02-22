<?php
declare (strict_types = 1);

namespace Cyndaron\Kaartverkoop;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class ConcertController extends Controller
{
    public function routeGet()
    {
        $id = intval(Request::getVar(2) ?: Util::getLatestConcertId());

        if ($this->action === 'getInfo')
        {
            $this->getConcertInfo($id);
        }
        else if ($this->action === 'order')
        {
            new KaartenBestellenPagina($id);
        }
        else if (User::isAdmin())
        {
            switch ($this->action)
            {
                case 'viewOrders':
                    new OverzichtBestellingenPagina($id);
                    break;
                case 'viewReservedSeats':
                    new GereserveerdePlaatsen($id);
                    break;
            }
        }
    }

    public function routePost()
    {
        switch ($this->action)
        {
            case '';
        }
    }

    protected function getConcertInfo(int $concertId)
    {
        $concertInfo = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM kaartverkoop_concerten WHERE id=?', [$concertId]);
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC', [$concertId]);

        $answer = [
            'kaartsoorten' => [],
            'bezorgenVerplicht' => boolval($concertInfo['bezorgen_verplicht']),
            'standaardVerzendkosten' => $concertInfo['verzendkosten'],
            'toeslagGereserveerdePlaats' => $concertInfo['toeslag_gereserveerde_plaats']
        ];

        foreach ($ticketTypes as $kaartsoort)
        {
            $answer['kaartsoorten'][] = [
                'id' => $kaartsoort['id'],
                'prijs' => $kaartsoort['prijs']
            ];
        }

        echo json_encode($answer);
    }
}