<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\Repository;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Ticketsale\Util;
use Cyndaron\User\UserSession;
use function assert;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly ImageExtractor $imageExtractor,
        private readonly UserSession $userSession,
        private readonly Repository $repository,
    ) {
    }

    public function save(int|null $id): int
    {
        $concert = $this->repository->fetchOrCreate(Concert::class, $id);
        $concert->name = $this->post->getHTML('titel');
        $concert->description = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $concert->descriptionWhenClosed = $this->imageExtractor->process($this->post->getHTML('descriptionWhenClosed'));
        $concert->openForSales = $this->post->getBool('openForSales');
        $concert->deliveryCost = $this->post->getFloat('deliveryCost');
        $concert->hasReservedSeats = $this->post->getBool('hasReservedSeats');
        $concert->reservedSeatCharge = $this->post->getFloat('reservedSeatCharge');
        $concert->reservedSeatsAreSoldOut = $this->post->getBool('reservedSeatsAreSoldOut');
        $concert->numFreeSeats = $this->post->getInt('numFreeSeats');
        $concert->numReservedSeats = $this->post->getInt('numReservedSeats');
        $concert->deliveryCostInterface = $this->post->getSimpleString('deliveryCostInterface');
        $concert->date = $this->post->getSimpleString('date');
        $concert->locationId = $this->post->getInt('locationId');
        $concert->ticketInfo = $this->post->getHTML('ticketInfo');

        $delivery = TicketDelivery::from($this->post->getInt('delivery'));
        if ($delivery === TicketDelivery::COLLECT_OR_DELIVER)
        {
            $concert->forcedDelivery = false;
            $concert->digitalDelivery = false;
        }
        elseif ($delivery === TicketDelivery::FORCED_PHYSICAL)
        {
            $concert->forcedDelivery = true;
            $concert->digitalDelivery = false;
        }
        elseif ($delivery === TicketDelivery::DIGITAL)
        {
            $concert->forcedDelivery = false;
            $concert->digitalDelivery = true;
        }

        if ($concert->secretCode === '')
        {
            $concert->secretCode = Util::generateSecretCode();
        }

        try
        {
            $this->repository->save($concert);
            $this->userSession->addNotification('Concert opgeslagen.');
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Fout bij opslaan concert');
        }

        $this->returnUrl = '/concert/order/' . $concert->id;

        assert($concert->id !== null);
        return $concert->id;
    }
}
