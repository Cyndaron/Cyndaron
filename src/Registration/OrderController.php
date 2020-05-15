<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected array $postRoutes = [
        'add' => ['level' => UserLevel::ANONYMOUS, 'function' => 'add'],
    ];
    protected array $apiPostRoutes = [
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
        'setApprovalStatus' => ['level' => UserLevel::ADMIN, 'function' => 'setApprovalStatus'],
        'setIsPaid' => ['level' => UserLevel::ADMIN, 'function' => 'setIsPaid'],
    ];

    protected function add(): Response
    {
        $eventId = (int)Request::post('event_id');
        try
        {
            $this->processOrder($eventId);

            $page = new Page(
                'Inschrijving verwerkt',
                'Hartelijk dank voor uw inschrijving. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw inschrijving en betaalinformatie.'
            );
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new Page('Fout bij verwerken inschrijving', $e->getMessage());
            return new Response($page->render());
        }
    }

    /**
     * @param $eventId
     * @return bool
     * @throws Exception
     */
    private function processOrder($eventId): bool
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
        $order->currentChoir = Request::post('currentChoir');
        $order->choirPreference = Request::post('choirPreference');
        $order->approvalStatus = $eventObj->requireApproval ? Order::APPROVAL_UNDECIDED : Order::APPROVAL_APPROVED;

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

        return $order->sendConfirmationMail($orderTotal, $orderTicketTypes);
    }

    private function checkForm(Event $event): array
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

    public function delete(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->delete();

        return new JsonResponse();
    }

    public function setApprovalStatus(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $status = (int)Request::post('status');
        switch ($status)
        {
            case Order::APPROVAL_APPROVED:
                $order->setApproved();
                break;
            case Order::APPROVAL_DISAPPROVED:
                $order->setDisapproved();
                break;
        }

        return new JsonResponse();
    }

    public function setIsPaid(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $order->setIsPaid();

        return new JsonResponse();
    }
}