<?php
declare (strict_types = 1);

namespace Cyndaron\Concerts;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class OrderController extends Controller
{
    protected $minLevelPost = UserLevel::ANONYMOUS;

    public function routePost()
    {
        if ($this->action === 'add')
        {
            $concertId = intval(Request::post('concert_id'));
            $this->addAction($concertId);
        }
        else if (User::isAdmin())
        {
            $id = intval(Request::getVar(2));
            /** @var Order $order */
            $order = Order::loadFromDatabase($id);

            switch ($this->action)
            {
                case 'setIsPaid':
                    $order->setIsPaid();
                    break;
                case 'setIsSent':
                    $order->setIsSent();
                    break;
                case 'delete':
                    $order->delete();
                    break;

            }
        }
    }

    private function addAction(int $concertId)
    {
        try
        {
            $this->processOrder($concertId);

            $page = new Page(
                'Bestelling verwerkt',
                'Hartelijk dank voor uw bestelling. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw bestelling en betaalinformatie.'
            );
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
        catch (\Exception $e)
        {
            $page = new Page('Fout bij verwerken bestelling', $e->getMessage());
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
    }

    /**
     * @param $concert_id
     * @throws \Exception
     */
    private function processOrder($concert_id)
    {
        if (Request::postIsEmpty())
        {
            throw new \Exception('De bestellingsgegevens zijn niet goed aangekomen.');
        }

        /** @var Concert $concertObj */
        $concertObj = Concert::loadFromDatabase($concert_id);
        $concert = $concertObj->asArray();

        if ($concert['open_voor_verkoop'] == false)
        {
            throw new \Exception('De verkoop voor dit concert is helaas gesloten, u kunt geen kaarten meer bestellen.');
        }

        $postcode = Request::post('postcode');
        $buitenland = (Request::post('land') === 'buitenland') ? true : false;
        $ophalenDoorKoorlid = Request::post('ophalen_door_koorlid') ? true : false;
        $ophalenDoorKoorlid = $buitenland ? true : $ophalenDoorKoorlid;
        $naam_koorlid = Request::post('naam_koorlid');

        $incorrecteVelden = $this->checkFormulier($concert['bezorgen_verplicht'], $ophalenDoorKoorlid);
        if (!empty($incorrecteVelden))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $incorrecteVelden) . '.';
            throw new \Exception($message);
        }

        $totaalprijs = 0.0;
        $totaalAantalKaarten = 0;

        if ($concert['bezorgen_verplicht'])
        {
            $woontInWalcheren = ($buitenland) ? false : Util::postcodeIsWithinWalcheren($postcode);

            if ($woontInWalcheren)
            {
                $payForDelivery = false;
                $ophalenDoorKoorlid = false;
            }
            else
            {
                if ($ophalenDoorKoorlid)
                {
                    $payForDelivery = false;
                }
                else
                {
                    $payForDelivery = true;
                }
            }
        }
        else
        {
            $payForDelivery = Request::post('bezorgen') ? true : false;
        }
        $deliveryPrice = $payForDelivery ? $concert['verzendkosten'] : 0.0;
        $reserveSeats = Request::post('gereserveerde_plaatsen') ? 1 : 0;
        $toeslag_gereserveerde_plaats = ($reserveSeats == 1) ? $concert['toeslag_gereserveerde_plaats'] : 0;
        $bestelling_kaartsoorten = [];
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC', [$concert_id]);
        foreach ($ticketTypes as $ticketType)
        {
            $bestelling_kaartsoorten[$ticketType['id']] = intval(Request::post('kaartsoort-' . $ticketType['id']));
            $totaalprijs += $bestelling_kaartsoorten[$ticketType['id']] * ($ticketType['prijs'] + $deliveryPrice + $toeslag_gereserveerde_plaats);
            $totaalAantalKaarten += $bestelling_kaartsoorten[$ticketType['id']];
        }

        if ($totaalprijs <= 0)
        {
            throw new \Exception('U heeft een bestelling van 0 kaarten geplaatst of het formulier is niet goed aangekomen.');
        }

        $emailadres = Request::post('e-mailadres');
        $achternaam = Request::post('achternaam');
        $voorletters = Request::post('voorletters');
        $straatnaam_en_huisnummer = Request::post('straatnaam_en_huisnummer');
        $postcode = Request::post('postcode');
        $woonplaats = Request::post('woonplaats');
        $opmerkingen = Request::post('opmerkingen');

        $orderId = (int)DBConnection::doQuery('INSERT INTO kaartverkoop_bestellingen
            (`concert_id`,  `achternaam`,     `voorletters`,     `e-mailadres`,     `straat_en_huisnummer`, `postcode`, `woonplaats`,     `thuisbezorgen`,         `gereserveerde_plaatsen`,             `ophalen_door_koorlid`,    `naam_koorlid`,    `woont_in_buitenland`,    `opmerkingen`) VALUES
            (?,             ?,                 ?,                 ?,                 ?,                     ?,          ?,                 ?,                       ?,                                   ?,                         ?,                 ?,                        ?)',
            [$concert_id, $achternaam, $voorletters, $emailadres, $straatnaam_en_huisnummer, $postcode, $woonplaats, ($payForDelivery ? 1 : 0), $reserveSeats, $ophalenDoorKoorlid, $naam_koorlid, $buitenland, $opmerkingen]);

        foreach ($ticketTypes as $ticketType)
        {
            if ($bestelling_kaartsoorten[$ticketType['id']] > 0)
            {
                DBConnection::doQuery('INSERT INTO kaartverkoop_bestellingen_kaartsoorten(`bestelling_id`, `kaartsoort_id`, `aantal`) VALUES(?, ?, ?)', [$orderId, $ticketType['id'], $bestelling_kaartsoorten[$ticketType['id']]]);
            }
        }

        $reservedSeats = null;
        if ($reserveSeats == 1)
        {
            $reservedSeats = $concertObj->reserveSeats($orderId, $totaalAantalKaarten);
            if ($reservedSeats === null)
            {
                DBConnection::doQuery('UPDATE kaartverkoop_bestellingen SET gereserveerde_plaatsen = 0 WHERE id=?', [$orderId]);
                $totaalprijs -= $totaalAantalKaarten * $toeslag_gereserveerde_plaats;
                $reserveSeats = -1;
            }
        }

        $this->sendMail($payForDelivery, $concert, $ophalenDoorKoorlid, $naam_koorlid, $reserveSeats, $reservedSeats, $totaalprijs, $orderId, $ticketTypes, $bestelling_kaartsoorten, $achternaam, $voorletters, $straatnaam_en_huisnummer, $postcode, $woonplaats, $opmerkingen, $emailadres);
    }

    private function checkFormulier($bezorgenVerplicht = false, $ophalenDoorKoorlid = false)
    {
        $incorrecteVelden = [];
        if (strtoupper(Request::post('antispam')) !== 'VLISSINGEN')
        {
            $incorrecteVelden[] = 'Antispam';
        }

        if (strlen(Request::post('achternaam')) === 0)
        {
            $incorrecteVelden[] = 'Achternaam';
        }

        if (strlen(Request::post('voorletters')) === 0)
        {
            $incorrecteVelden[] = 'Voorletters';
        }

        if (strlen(Request::post('e-mailadres')) === 0)
        {
            $incorrecteVelden[] = 'E-mailadres';
        }

        if ((!$bezorgenVerplicht && Request::post('bezorgen')) || ($bezorgenVerplicht && !$ophalenDoorKoorlid))
        {
            if (strlen(Request::post('straatnaam_en_huisnummer')) === 0)
            {
                $incorrecteVelden[] = 'Straatnaam en huisnummer';
            }

            if (strlen(Request::post('postcode')) === 0)
            {
                $incorrecteVelden[] = 'Postcode';
            }

            if (strlen(Request::post('woonplaats')) === 0)
            {
                $incorrecteVelden[] = 'Woonplaats';
            }
        }
        return $incorrecteVelden;
    }

    /**
     * @param bool $bezorgen
     * @param array $concert
     * @param bool $ophalenDoorKoorlid
     * @param $naam_koorlid
     * @param int $reserveSeats
     * @param array|null $reservedSeats
     * @param $totaalprijs
     * @param $orderId
     * @param array $ticketTypes
     * @param array $bestelling_kaartsoorten
     * @param $achternaam
     * @param $voorletters
     * @param $straatnaam_en_huisnummer
     * @param $postcode
     * @param $woonplaats
     * @param $opmerkingen
     * @param $emailadres
     */
    private function sendMail(bool $bezorgen, array $concert, bool $ophalenDoorKoorlid, $naam_koorlid, int $reserveSeats, ?array $reservedSeats, float $totaalprijs, $orderId, array $ticketTypes, array $bestelling_kaartsoorten, $achternaam, $voorletters, $straatnaam_en_huisnummer, $postcode, $woonplaats, $opmerkingen, $emailadres): void
    {
        if ($bezorgen || ($concert['bezorgen_verplicht'] && !$ophalenDoorKoorlid))
        {
            $opstuurtekst = 'naar uw adres verstuurd worden';
        }
        elseif ($concert['bezorgen_verplicht'] && $ophalenDoorKoorlid)
        {
            $opstuurtekst = 'worden meegegeven aan ' . $naam_koorlid;
        }
        else
        {
            $opstuurtekst = 'voor u klaargelegd worden bij de ingang van de kerk';
        }

        $voor_u_reserveerde_plaatsen = '';
        if ($reserveSeats === 1)
        {
            $voor_u_reserveerde_plaatsen = PHP_EOL . PHP_EOL . 'De volgende plaatsen zijn voor u gereserveerd: ';
            $voor_u_reserveerde_plaatsen .= implode(', ', $reservedSeats) . '.';
        }
        elseif ($reserveSeats === -1)
        {
            $voor_u_reserveerde_plaatsen = PHP_EOL . PHP_EOL . 'Er waren helaas niet voldoende plaatsen om te reserveren. De gerekende toeslag voor gereserveerde kaarten is weer van het totaalbedrag afgetrokken.';
        }

        $text = 'Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging.
Na betaling zullen uw kaarten ' . $opstuurtekst . '.' . $voor_u_reserveerde_plaatsen . '

Gebruik bij het betalen de volgende gegevens:
   Rekeningnummer: NL06INGB0000545925 t.n.v. Vlissingse Oratorium Vereniging
   Bedrag: ' . Util::formatEuroPlainText($totaalprijs) . '
   Onder vermelding van: bestellingsnummer ' . $orderId . '



Hieronder volgt een overzicht van uw bestelling.

Bestellingsnummer: ' . $orderId . '

Kaartsoorten:
';
        foreach ($ticketTypes as $ticketType)
        {
            if ($bestelling_kaartsoorten[$ticketType['id']] > 0)
            {
                $text .= '   ' . $ticketType['naam'] . ': ' . $bestelling_kaartsoorten[$ticketType['id']] . ' à ' . Util::formatEuroPlainText((float)$ticketType['prijs']) . PHP_EOL;
            }
        }
        if (!$concert['bezorgen_verplicht'])
        {
            $text .= PHP_EOL . 'Kaarten bezorgen: ' . Util::boolToText($bezorgen);
        }

        $text .= PHP_EOL . 'Gereserveerde plaatsen: ' . $reserveSeats == 1 ? 'Ja' : 'Nee' . PHP_EOL;
        $text .= 'Totaalbedrag: ' . Util::formatEuroPlainText($totaalprijs) . '

Achternaam: ' . $achternaam . '
Voorletters: ' . $voorletters . PHP_EOL . PHP_EOL;

        $extraFields = [
            'Straatnaam en huisnummer' => $straatnaam_en_huisnummer,
            'Postcode' => $postcode,
            'Woonplaats' => $woonplaats,
            'Opmerkingen' => $opmerkingen,
        ];

        foreach ($extraFields as $description => $contents)
        {
            if (!empty($contents))
            {
                $text .= $description . ': ' . $contents . PHP_EOL;
            }
        }

        mail($emailadres, 'Bestelling concertkaarten', $text, Order::MAIL_HEADERS);
    }
}