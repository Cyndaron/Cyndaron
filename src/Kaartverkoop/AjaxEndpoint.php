<?php
namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;
use Cyndaron\Request;

class AjaxEndpoint
{
    protected $connectie;

    public function __construct()
    {
        $actie = Request::geefPostVeilig('actie');
        $concertId = intval(Request::geefPostVeilig('concertId'));
        $this->connectie = DBConnection::getPdo();

        switch ($actie)
        {
            case 'geefKaartsoorten':
                $this->geefKaartsoorten($concertId);
                break;
        }
    }

    protected function geefKaartsoorten(int $concertId)
    {
        $prep = $this->connectie->prepare('SELECT * FROM kaartverkoop_concerten WHERE id=?');
        $prep->execute([$concertId]);
        $concert_info = $prep->fetch();

        $prep = $this->connectie->prepare('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC');
        $prep->execute([$concertId]);
        $answer = [
            'kaartsoorten' => [],
            'bezorgenVerplicht' => boolval($concert_info['bezorgen_verplicht']),
            'standaardVerzendkosten' => $concert_info['verzendkosten'],
            'toeslagGereserveerdePlaats' => $concert_info['toeslag_gereserveerde_plaats']
        ];

        foreach ($prep->fetchAll() as $kaartsoort)
        {
            $answer['kaartsoorten'][] = [
                'id' => $kaartsoort['id'],
                'prijs' => $kaartsoort['prijs']
            ];
        }

        echo json_encode($answer);
    }
}