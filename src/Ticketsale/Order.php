<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use \Exception;

class Order extends Model
{
    const TABLE = 'ticketsale_orders';
    const TABLE_FIELDS = ['concert_id', 'delivery', 'deliveryByMember', 'deliveryMemberName', 'email'];

    const MAIL_HEADERS = [
        'From' => '"Vlissingse Oratorium Vereniging" <noreply@vlissingse-oratoriumvereniging.nl>',
        'Content-Type' => 'text/plain; charset="UTF-8"',
    ];

    public $concert_id;
    public $delivery;
    public $deliveryByMember;
    public $deliveryMemberName;
    public $email;

    public function setIsPaid()
    {
        if ($this->id === null)
        {
            throw new Exception('ID is null!');
        }

        $concert = new Concert((int)$this->concert_id);
        $concert->load();

        DBConnection::doQuery('UPDATE ticketsale_orders SET `isPaid`=1 WHERE id=?', [$this->id]);

        $text = "Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging. Wij hebben uw betaling in goede orde ontvangen.\n";
        if ($this->delivery || ($concert->forcedDelivery && $this->deliveryByMember == 0))
        {
            $text .= 'Uw kaarten zullen zo spoedig mogelijk worden opgestuurd.';
        }
        elseif ($concert->forcedDelivery && $this->deliveryByMember == 1)
        {
            $text .= 'Uw kaarten zullen worden meegegeven aan ' . $this->deliveryMemberName . '.';
        }
        else
        {
            $text .= 'Uw kaarten zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        $extraheaders = 'From: "Vlissingse Oratorium Vereniging" <noreply@vlissingse-oratoriumvereniging.nl>;' . "\n" .
            'Content-Type: text/plain; charset="UTF-8";';
        mail($this->email, 'Betalingsbevestiging', $text, $extraheaders);
    }

    public function setIsSent()
    {
        if ($this->id === null)
        {
            throw new Exception('id is null!');
        }

        DBConnection::doQuery('UPDATE ticketsale_orders SET `isDelivered`=1 WHERE id=?', [$this->id]);
    }
}