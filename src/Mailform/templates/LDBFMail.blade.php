<!DOCTYPE html><html lang="nl"><head><title>Aanvraag</title><body><table>
    <tbody>
        <tr>
            <td>De heer/mevrouw:</td>
            <td>{{ $geslacht }}</td>
        </tr>
        <tr>
            <td>Voorletters:</td>
            <td>{{ $voorletters }}</td>
        </tr>
        <tr>
            <td>Achternaam:</td>
            <td>{{ $achternaam }}</td>
        </tr>
        <tr>
            <td>Adres:</td>
            <td>{{ $adres }}</td>
        </tr>
        <tr>
            <td>Postcode</td>
            <td>{{ $postcode }}</td>
        </tr>
        <tr>
            <td>Woonplaats</td>
            <td>{{ $woonplaats }}</td>
        </tr>
        <tr>
            <td>Telefoon/GSM:</td>
            <td>{{ $telefoon }}</td>
        </tr>
        <tr>
            <td>E-mailadres:</td>
            <td>{{ $emailadres }}</td>
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
            <td>{{ $bvoornamen }}</td>
        </tr>
        <tr>
            <td>Achternaam:</td>
            <td>{{ $bachternaam }}</td>
        </tr>
        <tr>
            <td>Woonadres:</td>
            <td>{{ $bwoonadres }}</td>
        </tr>
        <tr>
            <td>Geboortedatum:</td>
            <td>{{ $geboortedag }}-{{ $geboortemaand }}-{{ $geboortejaar }}</td>
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
            <td>{{ $studie }}</td>
        </tr>
        <tr>
            <td>Betrokkene volgde hiervoor:</td>
            <td>{{ $lessen }} lessen</td>
        </tr>
        <tr>
            <td>Eerder werd les gevolgd bij:</td>
            <td>{{ $lesdocent }}</td>
        </tr>
        <tr>
            <td>Aantal gevolgde lesjaren:</td>
            <td>{{ $aantal }}</td>
        </tr>
        <tr>
            <td>Vervolgstudie bij:</td>
            <td>{{ $vervolg }}</td>
        </tr>
        <tr>
            <td colspan="2">{{ $soort }}</td>
        </tr>
        <tr>
            <td>Huurgebruik / Aanschaf instrument:</td>
            <td>{{ $huur }}</td>
        </tr>
        <tr>
            <td>Anderszins:</td>
            <td>{{ $anderszins }}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
        <tr>
            <td>Aanvrager verklaart hierbij dat het bruto gezinsinkomen:</td>
            <td>&euro; {{ $gezinsinkomen }} bedraagt.</td>
        </tr>
        <tr>
            <td colspan="2"><b>Aanvraag voor financi&euml;le ondersteuning wordt niet toegekend, indien het gezinsinkomen meer dan 120% van de toepasselijke bijstandsnorm bedraagt.</b></td>
        </tr>
        <tr>
            <td>Aanvrager meldt hierbij dat er ook een aanvraag is ingediend bij:<br />
                (indien van toepassing)</td>
            <td>{{ $ookaanvraag }}</td>
        </tr>
    </tbody>
</table></body></html>