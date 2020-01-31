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

    public int $concert_id;
    public string $delivery;
    public bool $deliveryByMember;
    public string $deliveryMemberName;
    public string $email;

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
        if ($this->delivery || ($concert->forcedDelivery && !$this->deliveryByMember))
        {
            $text .= 'Uw kaarten zullen zo spoedig mogelijk worden opgestuurd.';
        }
        elseif ($concert->forcedDelivery && $this->deliveryByMember)
        {
            $text .= 'Uw kaarten zullen worden meegegeven aan ' . $this->deliveryMemberName . '.';
        }
        else
        {
            $text .= 'Uw kaarten zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        return Util::mail($this->email, 'Betalingsbevestiging', $text);
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