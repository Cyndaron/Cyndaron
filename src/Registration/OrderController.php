<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
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

    protected function add(RequestParameters $post): Response
    {
        try
        {
            $this->processOrder($post);

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
     * @param RequestParameters $post
     * @return bool
     * @throws Exception
     */
    private function processOrder(RequestParameters $post): bool
    {
        if ($post->isEmpty())
        {
            throw new Exception('De inschrijvingsgegevens zijn niet goed aangekomen.');
        }

        $eventId = $post->getInt('event_id');

        /** @var Event $eventObj */
        $eventObj = Event::loadFromDatabase($eventId);
        if ($eventObj === null)
        {
            throw new Exception('Evenement niet gevonden!');
        }

        if (!$eventObj->openForRegistration)
        {
            throw new Exception('De verkoop voor dit evenement is helaas gesloten, u kunt geen kaarten meer bestellen.');
        }

        $errorFields = $this->checkForm($eventObj, $post);
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
            $orderTicketTypes[$ticketType->id] = $post->getInt('tickettype-' . $ticketType->id);
        }

        $order = new Order();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $order->eventId = $eventObj->id;
        $order->lastName = $post->getSimpleString('lastName');
        $order->initials = $post->getInitials('initials');
        $order->vocalRange = $post->getSimpleString('vocalRange');
        $order->registrationGroup = $post->getInt('registrationGroup');
        $order->birthYear = $post->getInt('birthYear') ?: null;
        $order->lunch = $post->getBool( 'lunch');
        if ($order->lunch)
        {
            $order->lunchType = $post->getSimpleString('lunchType');
        }
        $order->bhv = $post->getBool('bhv');
        $order->kleinkoor = $post->getBool('kleinkoor');
        $order->kleinkoorExplanation = $post->getSimpleString('kleinkoorExplanation');
        $order->participatedBefore = $post->getBool('participatedBefore');
        $order->numPosters = $post->getInt('numPosters');
        $order->email = $post->getEmail('email');
        $order->street = $post->getSimpleString('street');
        $order->houseNumber = $post->getInt('houseNumber');
        $order->houseNumberAddition = $post->getSimpleString('houseNumberAddition');
        $order->postcode = $post->getPostcode('postcode');
        $order->city = $post->getSimpleString('city');
        $order->comments = $post->getHTML('comments');
        $order->currentChoir = $post->getSimpleString('currentChoir');
        $order->choirPreference = $post->getSimpleString('choirPreference');
        $order->approvalStatus = $eventObj->requireApproval ? Order::APPROVAL_UNDECIDED : Order::APPROVAL_APPROVED;

        $orderTotal = $order->calculateTotal($orderTicketTypes);
        if ($orderTotal <= 0)
        {
            throw new Exception('Het formulier is niet goed aangekomen.');
        }

        if (!$order->save())
        {
            $msg = var_export(DBConnection::errorInfo(), true);
            throw new Exception($msg . 'Opslaan inschrijving mislukt!');
        }

        foreach ($ticketTypes as $ticketType)
        {
            if ($orderTicketTypes[$ticketType->id] > 0)
            {
                $ott = new OrderTicketType();
                /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
                $ott->orderId = $order->id;
                /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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

    private function checkForm(Event $event, RequestParameters $post): array
    {
        $errorFields = [];
        if (strcasecmp($post->getAlphaNum('antispam'), $event->getAntispam()) !== 0)
        {
            $errorFields[] = 'Antispam';
        }

        if ($post->getSimpleString('lastName') === '')
        {
            $errorFields[] = 'Achternaam';
        }

        if ($post->getInitials('initials') === '')
        {
            $errorFields[] = 'Voorletters';
        }

        if ($post->getEmail('email') === '')
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

    public function setApprovalStatus(RequestParameters $post): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        /** @var Order $order */
        $order = Order::loadFromDatabase($id);
        $status = $post->getInt('status');
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