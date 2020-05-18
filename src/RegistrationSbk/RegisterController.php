<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserLevel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
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
                'Aanmelding verwerkt',
                'Hartelijk dank voor je aanmelding. Je ontvangt binnen enkele minuten een e-mail met een bevestiging van je aanmelding.'
            );
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new Page('Fout bij verwerken aanmelding', $e->getMessage());
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
            throw new Exception('De aanmeldingsgegevens zijn niet goed aangekomen.');
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
            throw new Exception('De aanmelding voor dit evenement is helaas gesloten, je kunt je niet meer aanmelden.');
        }

        $errorFields = $this->checkForm($eventObj, $post);
        if (!empty($errorFields))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $errorFields) . '.';
            throw new Exception($message);
        }

        $order = new Registration();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $order->eventId = $eventObj->id;
        $order->lastName = $post->getSimpleString('lastName');
        $order->initials = $post->getInitials('initials');
        $order->vocalRange = $post->getSimpleString('vocalRange');
        $order->email = $post->getEmail('email');
        $order->phone = $post->getPhone('phone');
        $order->city = $post->getSimpleString('city');
        $order->currentChoir = $post->getSimpleString('currentChoir');
        $order->choirExperience = $post->getInt('choirExperience');
        $order->performedBefore = $post->getBool('performedBefore');
        $order->comments = $post->getSimpleString('comments');

        $result = $order->save();
        if ($result === false)
        {
            $msg = var_export(DBConnection::errorInfo(), true);
            throw new Exception($msg . 'Opslaan aanmelding mislukt!');
        }

        return $order->sendConfirmationMail();
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
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $registration->delete();

        return new JsonResponse();
    }

    public function setApprovalStatus(RequestParameters $post): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $status = $post->getInt('status');
        switch ($status)
        {
            case Registration::APPROVAL_APPROVED:
                $registration->setApproved();
                break;
            case Registration::APPROVAL_DISAPPROVED:
                $registration->setDisapproved();
                break;
        }

        return new JsonResponse();
    }

    public function setIsPaid(): JsonResponse
    {
        $id = $this->queryBits->getInt(2);
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $registration->setIsPaid();

        return new JsonResponse();
    }
}