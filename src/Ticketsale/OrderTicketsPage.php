<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Setting;
use Cyndaron\User\User;


class OrderTicketsPage extends Page
{
    public function __construct(int $concertId)
    {
        $this->addScript('/src/Ticketsale/OrderTicketsPage.js');

        $concert = new Concert($concertId);
        $concert->load();
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_tickettypes WHERE concertId=? ORDER BY price DESC', [$concertId]);
        $controls = sprintf('<a href="/editor/concert/%d" class="btn btn-outline-cyndaron" title="Dit concert bewerken" role="button"><span class="glyphicon glyphicon-pencil"></span></a>', $concertId);


        parent::__construct('Kaarten bestellen: ' . $concert->name);
        $this->setTitleButtons($controls);
        $this->showPrePage();

        if (!$concert->openForSales)
        {
            if ($concert->descriptionWhenClosed)
            {
                echo $concert->descriptionWhenClosed;
            }
            else
            {
                echo 'Voor dit concert kunt u kaarten kopen aan de kassa in de St. Jacobskerk voor aanvang van het concert. Bestellen via de website is voor dit concert niet meer mogelijk.';
            }

            $this->showPostPage();
            die();
        }

        echo '<p>' . $concert->description . '</p>';
        ?>

        <h3>Vrije plaatsen en gereserveerde plaatsen</h3>
        <p><?php printf(Setting::get('ticketsale_reservedSeatsDescription'), $concert->numReservedSeats)?></p>

        <br/>
        <form method="post" action="/concert-order/add" class="form-horizontal" id="kaartenbestellen">
            <input type="hidden" name="csrfToken" value="<?=User::getCSRFToken('concert-order', 'add')?>"/>
            <h3>Kaartsoorten:</h3>
            <input type="hidden" id="concertId" name="concert_id" value="<?php echo $concertId; ?>"/>
            <table class="kaartverkoop table table-striped">
                <tr>
                    <th>Kaartsoort:</th>
                    <th>Prijs per stuk:</th>
                    <th>Aantal:</th>
                </tr>
                <?php
                foreach ($ticketTypes as $kaartsoort)
                {
                    printf('
                        <tr>
                            <td>%1$s</td>
                            <td>%2$s</td>
                            <td>
                                <input class="numTickets form-control form-control-inline" readonly="readonly" size="2" name="tickettype-%3$d" id="tickettype-%3$d" value="0"/>
                                <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-increase" data-kaartsoort="%3$d"><span class="glyphicon glyphicon-plus"></span></button>
                                <button type="button" class="numTickets btn btn-outline-cyndaron numTickets-decrease" data-kaartsoort="%3$d"><span class="glyphicon glyphicon-minus"></span></button>
                            </td>
                        </tr>',
                        $kaartsoort['name'], Util::formatEuro($kaartsoort['price']), $kaartsoort['id']);
                }
                ?>
            </table>
            <div <?= $concert->forcedDelivery ? 'style="display:none"' : ''; ?>>
                <input id="bezorgen" name="bezorgen" type="checkbox" value="1" class="recalculateTotal">
                <label for="bezorgen">
                    Bezorg mijn kaarten thuis (meerprijs
                    van <?php echo Util::formatEuro($concert->deliveryCost); ?> per kaart)
                </label>
            </div>

            <?php if ($concert->hasReservedSeats): ?>
                <?php if ($concert->reservedSeatsAreSoldOut): ?>
                    <input id="hasReservedSeats" name="hasReservedSeats" style="display:none;"
                           type="checkbox" value="1"/>
                    U kunt voor dit concert nog kaarten voor vrije plaatsen kopen. <b>De gereserveerde plaatsen zijn inmiddels uitverkocht.</b>
                <?php else: ?>
                    <div class="form-group form-check">
                        <input id="hasReservedSeats" class="recalculateTotal form-check-input" name="hasReservedSeats"
                               type="checkbox" value="1"/>
                        <label class="form-check-label" for="hasReservedSeats">
                            Gereserveerde plaats met stoelnummer in het middenschip van de kerk (meerprijs
                            van <?php echo Util::formatEuro($concert->reservedSeatCharge); ?> per kaart)
                        </label>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <input id="hasReservedSeats" type="hidden" value="0">
            <?php endif; ?>

            <?php if ($concert->forcedDelivery): ?>
                <h3>Bezorging</h3>
                <p>
                    Bij dit concert is het alleen mogelijk om uw kaarten te laten thuisbezorgen. Als u op Walcheren
                    woont is dit gratis. Woont u buiten Walcheren, dan kost het
                    thuisbezorgen <?= Util::formatEuro($concert->deliveryCost); ?> per kaart.<br>Het is ook
                    mogelijk
                    om uw kaarten te laten ophalen door een koorlid. Dit is gratis.
                </p>

                <div class="radio">
                    <label for="country-nederland">
                        <input id="country-nederland" name="country" type="radio" value="nederland" checked />
                        Ik woon in Nederland
                    </label>
                </div>
                <div class="radio">
                    <label for="country-abroad">
                        <input id="country-abroad" name="country" type="radio" value="abroad"/>
                        Ik woon niet in Nederland
                    </label>
                </div>
                <br>


                <p class="postcode-related">
                    Vul hieronder uw postcode in om de totaalprijs te laten berekenen.
                </p>

                <div class="form-group row postcode-related">
                    <label class="col-sm-3 col-form-label" for="postcode">Postcode (verplicht):</label>
                    <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control form-control-inline"
                                                 maxlength="7"/></div>
                </div>

                <div id="deliveryByMember_div" style="display:none;">
                    <input id="deliveryByMember" name="deliveryByMember" type="checkbox" value="1"
                           class="recalculateTotal">
                    <label for="deliveryByMember">Mijn kaarten laten ophalen door een koorlid</label>
                    <br>

                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label" for="deliveryMemberName">Naam koorlid:</label>
                        <div class="col-sm-5"><input id="deliveryMemberName" name="deliveryMemberName" type="text"
                                                     class="form-control"/></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="well"><b>Totaalprijs:</b> <span id="prijsvak">€&nbsp;0,00</span></div>

            <h3>Uw gegevens (verplicht):</h3>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="lastName">Achternaam:</label>
                <div class="col-sm-5"><input id="lastName" name="lastName" class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="initials">Voorletters:</label>
                <div class="col-sm-5"><input id="initials" name="initials" class="form-control"/></div>
            </div>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="email">E-mailadres:</label>
                <div class="col-sm-5"><input id="email" name="email" type="email" class="form-control"/></div>
            </div>


            <h3 id="adresgegevensKop">Uw adresgegevens (nodig als u de kaarten wilt laten bezorgen):</h3>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="street">Straatnaam en huisnummer:</label>
                <div class="col-sm-5"><input id="street" name="street"
                                             class="form-control"/></div>
            </div>

            <?php if (!$concert->forcedDelivery): ?>
                <div class="form-group row">
                    <label class="col-sm-3 col-form-label" for="postcode">Postcode:</label>
                    <div class="col-sm-5"><input id="postcode" name="postcode" class="form-control"/></div>
                </div>
            <?php endif; ?>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="city">Woonplaats:</label>
                <div class="col-sm-5"><input id="city" name="city" class="form-control"/></div>
            </div>


            <h3>Verzenden:</h3>

            <p>Als u nog opmerkingen heeft kunt u deze hier kwijt.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="comments">Opmerkingen (niet verplicht):</label>
                <div class="col-sm-5"><textarea id="comments" name="comments" class="form-control"
                                                rows="4"></textarea></div>
            </div>

            <p>Om te voorkomen dat er spam wordt verstuurd met dit formulier<br/>wordt u verzocht in het onderstaande
                vak <span style="font-family:monospace;">Vlissingen</span> in te vullen.</p>

            <div class="form-group row">
                <label class="col-sm-3 col-form-label" for="antispam">Antispam:</label>
                <div class="col-sm-5"><input id="antispam" name="antispam" class="form-control"/></div>
            </div>

            <div class="col-sm-offset-2"><input id="verzendknop" class="btn btn-primary" type="submit"
                                                value="Bestellen"/></div>
        </form>
        <?php
        $this->showPostPage();
    }
}