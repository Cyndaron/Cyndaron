<?php
namespace Cyndaron\Mailform;

use Cyndaron\Page;
use Cyndaron\Request;

class VerwerkMailFormulierPaginaLDBF extends Page
{
    public function __construct()
    {
        if (!Request::postIsEmpty())
        {
            if (empty(Request::post('E-mailadres')))
            {
                parent::__construct('Formulier versturen mislukt');
                $this->body = 'U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.';
                $this->render();
            }
            else
            {
                $tekst = '<!DOCTYPE html><html lang="nl"><head><title>Aanvraag</title><body><table>
	<tbody>
		<tr>
			<td>De heer/mevrouw:</td>
			<td>' . Request::post('geslacht') . '</td>
		</tr>
		<tr>
			<td>Voorletters:</td>
			<td>' . Request::post('voorletters') . '</td>
		</tr>
		<tr>
			<td>Achternaam:</td>
			<td>' . Request::post('achternaam') . '</td>
		</tr>
		<tr>
			<td>Adres:</td>
			<td>' . Request::post('adres') . '</td>
		</tr>
		<tr>
			<td>Postcode</td>
			<td>' . Request::post('postcode') . '</td>
		</tr>
		<tr>
			<td>Woonplaats</td>
			<td>' . Request::post('woonplaats') . '</td>
		</tr>
		<tr>
			<td>Telefoon/GSM:</td>
			<td>' . Request::post('telefoon') . '</td>
		</tr>
		<tr>
			<td>E-mailadres:</td>
			<td>' . Request::post('E-mailadres') . '</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">Vraagt hierbij een financi&euml;le ondersteuning (*) aan bij het Leen de Broekert Fonds voor:</td>
		</tr>
		<tr>
			<td>Voornamen:</td>
			<td>' . Request::post('bvoornamen') . '</td>
		</tr>
		<tr>
			<td>Achternaam:</td>
			<td>' . Request::post('bachternaam') . '</td>
		</tr>
		<tr>
			<td>Woonadres:</td>
			<td>' . Request::post('bwoonadres') . '</td>
		</tr>
		<tr>
			<td>Geboortedatum:</td>
			<td>' . Request::post('geboortedag') . '-' . Request::post('geboortemaand') . '-' . Request::post('geboortejaar') . '</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td colspan="2">De financi&euml;le ondersteuning betreft een bijdrage ten behoeve van:</td>
		</tr>
		<tr>
			<td>Instrumentale/Vocale studie:</td>
			<td>' . Request::post('studie') . '</td>
		</tr>
		<tr>
			<td>Betrokkene volgde hiervoor:</td>
			<td>' . Request::post('lessen') . ' lessen</td>
		</tr>
		<tr>
			<td>Eerder werd les gevolgd bij:</td>
			<td>' . Request::post('lesdocent') . '</td>
		</tr>
		<tr>
			<td>Aantal gevolgde lesjaren:</td>
			<td>' . Request::post('aantal') . '</td>
		</tr>
		<tr>
			<td>Vervolgstudie bij:</td>
			<td>' . Request::post('vervolg') . '</td>
		</tr>
		<tr>
			<td colspan="2">' . Request::post('soort') . '</td>
		</tr>
		<tr>
			<td>Huurgebruik / Aanschaf instrument:</td>
			<td>' . Request::post('huur') . '</td>
		</tr>
		<tr>
			<td>Anderszins:</td>
			<td>' . Request::post('anderszins') . '</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Aanvrager verklaart hierbij dat het bruto gezinsinkomen:</td>
			<td>&euro; ' . Request::post('gezinsinkomen') . ' bedraagt.</td>
		</tr>
		<tr>
			<td colspan="2"><b>Aanvraag voor financi&euml;le ondersteuning wordt niet toegekend, indien het gezinsinkomen meer dan 120 % van de toepasselijke bijstandsnorm bedraagt.</b></td>
		</tr>
		<tr>
			<td>Aanvrager meldt hierbij dat er ook een aanvraag is ingediend bij:<br />
			(indien van toepassing)</td>
			<td>' . Request::post('ookaanvraag') . '</td>
		</tr>
	</tbody>
</table></body></html>';

                $extraheaders = 'From: "Website Leen de Broekert Fonds" <noreply@leendebroekertfonds.nl>' . "\n" .
                    'Content-Type: text/html; charset="UTF-8"';
                $extraheadersMail1 = $extraheaders . "\n" . 'Reply-To: ' . Request::post('E-mailadres');
                $extraheadersMail2 = $extraheaders . "\n" . 'Reply-To: voorzitter@leendebroekertfonds.nl';

                $mail1 = mail('voorzitter@leendebroekertfonds.nl', 'Nieuwe aanvraag', $tekst, $extraheadersMail1, '-fnoreply@leendebroekertfonds.nl');
                $mail2 = mail(Request::post('E-mailadres'), 'Kopie aanvraag', $tekst, $extraheadersMail2, '-fnoreply@leendebroekertfonds.nl');

                if ($mail1 && $mail2)
                {
                    parent::__construct('Formulier verstuurd');
                    $this->body = 'Het versturen is gelukt.';
                }
                else
                {
                    parent::__construct('Formulier versturen mislukt');
                    $this->body = 'Wegens een technisch probleem is het versturen niet gelukt';
                }
                $this->render();
            }
        }
        else
        {
            parent::__construct('Formulier versturen mislukt');
            $this->body = 'Ongeldig formulier.';
            $this->render();
        }
    }
}