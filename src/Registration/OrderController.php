<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Exception;

class OrderController extends Controller
{
    protected array $postRoutes = [
        'add' => ['level' => UserLevel::ANONYMOUS, 'function' => 'add'],
    ];

    protected function routePost()
    {
        $id = (int)Request::getVar(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);

        switch ($this->action)
        {
            case 'setIsPaid':
                $order->setIsPaid();
                break;
            case 'delete':
                $order->delete();
                break;

        }
    }

    protected function add()
    {
        $eventId = (int)Request::post('event_id');
        try
        {
            $this->processOrder($eventId);

            $page = new Page(
                'Inschrijving verwerkt',
                'Hartelijk dank voor uw inschrijving. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw inschrijving en betaalinformatie.'
            );
            $page->render();
        }
        catch (Exception $e)
        {
            $page = new Page('Fout bij verwerken inschrijving', $e->getMessage());
            $page->render();
        }
    }

    /**
     * @param $eventId
     * @throws Exception
     */
    private function processOrder($eventId)
    {
        if (Request::postIsEmpty())
        {
            throw new Exception('De inschrijvingsgegevens zijn niet goed aangekomen.');
        }

        /** @var Event $eventObj */
        $eventObj = Event::loadFromDatabase($eventId);

        if (!$eventObj->openForRegistration)
        {
            throw new Exception('De verkoop voor dit evenement is helaas gesloten, u kunt geen kaarten meer bestellen.');
        }

        $errorFields = $this->checkForm($eventObj);
        if (!empty($errorFields))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $errorFields) . '.';
            throw new Exception($message);
        }

        $orderTicketTypes = [];
        $ticketTypes = EventTicketType::loadByEvent($eventObj);
        foreach ($ticketTypes as $ticketType)
        {
            $orderTicketTypes[$ticketType->id] = (int)Request::post('tickettype-' . $ticketType->id);
        }

        $order = new Order();
        $order->eventId = $eventObj->id;
        $order->lastName = Request::post('lastName');
        $order->initials = Request::post('initials');
        $order->vocalRange = Request::post('vocalRange');
        $order->registrationGroup = (int)Request::post('registrationGroup');
        $order->birthYear = (int)Request::post('birthYear') ?: null;
        $order->lunch = (bool)filter_input(INPUT_POST, 'lunch', FILTER_VALIDATE_BOOLEAN);
        if ($order->lunch)
        {
            $order->lunchType = filter_input(INPUT_POST, 'lunchType', FILTER_SANITIZE_STRING);
        }
        $order->bhv = (bool)filter_input(INPUT_POST, 'bhv', FILTER_VALIDATE_BOOLEAN);
        $order->kleinkoor = (bool)filter_input(INPUT_POST, 'kleinkoor', FILTER_VALIDATE_BOOLEAN);
        $order->kleinkoorExplanation = Request::post('kleinkoorExplanation');
        $order->participatedBefore = (bool)filter_input(INPUT_POST, 'participatedBefore', FILTER_VALIDATE_BOOLEAN);
        $order->numPosters = (int)Request::post('numPosters');
        $order->email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $order->street = Request::post('street');
        $order->houseNumber = (int)Request::post('houseNumber');
        $order->houseNumberAddition = Request::post('houseNumberAddition');
        $order->postcode = Request::post('postcode');
        $order->city = Request::post('city');
        $order->comments = Request::post('comments');

        $orderTotal = $order->calculateTotal($orderTicketTypes);
        if ($orderTotal <= 0)
        {
            throw new Exception('Het formulier is niet goed aangekomen.');
        }

        $result = $order->save();
        if ($result === false)
        {
            $msg = var_export(DBConnection::errorInfo(), true);
            throw new Exception($msg . 'Opslaan inschrijving mislukt!');
        }

        foreach ($ticketTypes as $ticketType)
        {
            if ($orderTicketTypes[$ticketType->id] > 0)
            {
                $ott = new OrderTicketType();
                $ott->orderId = $order->id;
                $ott->tickettypeId = $ticketType->id;
                $ott->amount = $orderTicketTypes[$ticketType->id];
                $result = $ott->save();
                if ($result === false)
                {
                    throw new Exception('Opslaan kaarttypen mislukt!');
                }
            }
        }

        $order->sendConfirmationMail($orderTotal, $orderTicketTypes);
    }

    private function checkForm(Event $event)
    {
        $errorFields = [];
        if (strcasecmp(Request::post('antispam'),$event->getAntispam()) !== 0)
        {
            $errorFields[] = 'Antispam';
        }

        if (Request::post('lastName') === '')
        {
            $errorFields[] = 'Achternaam';
        }

        if (Request::post('initials') === '')
        {
            $errorFields[] = 'Voorletters';
        }

        if (Request::post('email') === '')
        {
            $errorFields[] = 'E-mailadres';
        }

        return $errorFields;
    }
}