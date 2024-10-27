<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Error\IncompleteData;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function assert;
use function implode;
use function min;
use function strcasecmp;

final class RegistrationController extends Controller
{
    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function add(RequestParameters $post, UrlInfo $urlInfo): Response
    {
        try
        {
            $eventId = $post->getInt('event_id');
            /** @var Event|null $eventObj */
            $eventObj = Event::fetchById($eventId);
            if ($eventObj === null)
            {
                throw new Exception('Evenement niet gevonden!');
            }

            $this->processRegistration($post, $urlInfo);

            $body = 'Hartelijk dank voor uw aanmelding. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw aanmelding en betaalinformatie.';
            if ($eventObj->hideRegistrationFee)
            {
                $body = 'Hartelijk dank voor uw aanmelding. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw aanmelding.';
            }

            $page = new SimplePage(
                'Aanmelding verwerkt',
                $body
            );
            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Fout bij verwerken aanmelding', $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
    }

    /**
     * @param RequestParameters $post
     * @throws Exception
     * @return bool
     */
    private function processRegistration(RequestParameters $post, UrlInfo $urlInfo): bool
    {
        if ($post->isEmpty())
        {
            throw new IncompleteData('De aanmeldingsgegevens zijn niet goed aangekomen.');
        }

        $eventId = $post->getInt('event_id');

        /** @var Event|null $eventObj */
        $eventObj = Event::fetchById($eventId);
        if ($eventObj === null)
        {
            throw new Exception('Evenement niet gevonden!');
        }

        if (!$eventObj->openForRegistration)
        {
            $warning = 'De aanmelding voor dit evenement is helaas gesloten, u kunt zich niet meer aanmelden.';
            throw new Exception($warning);
        }

        $errorFields = $this->checkForm($eventObj, $post);
        if (!empty($errorFields))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $errorFields) . '.';
            throw new IncompleteData($message);
        }

        /** @var array<int, int> $registrationTicketTypes */
        $registrationTicketTypes = [];
        $ticketTypes = EventTicketType::loadByEvent($eventObj);
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $registrationTicketTypes[$ticketType->id] = $post->getInt('tickettype-' . $ticketType->id);
        }

        assert($eventObj->id !== null);
        $registration = new Registration();
        $registration->eventId = $eventObj->id;
        $registration->lastName = $post->getSimpleString('lastName');
        // May double as first name!
        $registration->initials = $post->getSimpleString('initials');
        $registration->vocalRange = $post->getSimpleString('vocalRange');
        $registration->registrationGroup = $post->getInt('registrationGroup');
        $registration->birthYear = $post->getInt('birthYear') ?: null;
        $registration->lunch = $post->getBool('lunch');
        if ($registration->lunch)
        {
            $registration->lunchType = $post->getSimpleString('lunchType');
        }
        $registration->bhv = $post->getBool('bhv');
        $registration->kleinkoor = $post->getBool('kleinkoor');
        $registration->kleinkoorExplanation = $post->getSimpleString('kleinkoorExplanation');
        $registration->participatedBefore = min(9, $post->getInt('participatedBefore'));
        $registration->numPosters = $post->getInt('numPosters');
        $registration->email = $post->getEmail('email');
        $registration->phone = $post->getPhone('phone');
        $registration->street = $post->getSimpleString('street');
        $registration->houseNumber = $post->getInt('houseNumber');
        $registration->houseNumberAddition = $post->getSimpleString('houseNumberAddition');
        $registration->postcode = $post->getPostcode('postcode');
        $registration->city = $post->getSimpleString('city');
        $registration->currentChoir = $post->getSimpleString('currentChoir');
        $registration->choirPreference = $post->getSimpleString('choirPreference');
        $registration->choirExperience = $post->getInt('choirExperience');
        $registration->performedBefore = $post->getBool('performedBefore');
        $registration->comments = $post->getHTML('comments');
        $registration->approvalStatus = $eventObj->requireApproval ? Registration::APPROVAL_UNDECIDED : Registration::APPROVAL_APPROVED;

        $registrationTotal = $registration->calculateTotal($registrationTicketTypes);
        if ($registrationTotal === 0.00)
        {
            $registration->isPaid = true;
        }

        if (!$registration->save())
        {
            throw new DatabaseError('Opslaan aanmelding mislukt!');
        }

        assert($registration->id !== null);
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            if ($registrationTicketTypes[$ticketType->id] > 0)
            {
                $ott = new RegistrationTicketType();
                $ott->orderId = $registration->id;
                $ott->tickettypeId = $ticketType->id;
                $ott->amount = $registrationTicketTypes[$ticketType->id];
                $result = $ott->save();
                if (!$result)
                {
                    throw new DatabaseError('Opslaan kaarttypen mislukt!');
                }
            }
        }

        return $registration->sendIntroductionMail($urlInfo->domain, $registrationTotal, $registrationTicketTypes, $this->templateRenderer);
    }

    /**
     * @param Event $event
     * @param RequestParameters $post
     * @return string[]
     */
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

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = Registration::fetchById($id);
        $registration->delete();

        return new JsonResponse();
    }

    #[RouteAttribute('setApprovalStatus', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function setApprovalStatus(QueryBits $queryBits, RequestParameters $post, UrlInfo $urlInfo): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = Registration::fetchById($id);
        $status = $post->getInt('status');
        switch ($status)
        {
            case Registration::APPROVAL_APPROVED:
                $registration->setApproved($urlInfo->domain);
                break;
            case Registration::APPROVAL_DISAPPROVED:
                $registration->setDisapproved($urlInfo->domain);
                break;
        }

        return new JsonResponse();
    }

    #[RouteAttribute('setIsPaid', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function setIsPaid(QueryBits $queryBits, UrlInfo $urlInfo): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = Registration::fetchById($id);
        $registration->setIsPaid($urlInfo->domain);

        return new JsonResponse();
    }
}
