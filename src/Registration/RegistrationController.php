<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\View\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Util\Setting;
use Cyndaron\User\UserLevel;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function implode;
use function assert;
use function strcasecmp;

final class RegistrationController extends Controller
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
            $this->processRegistration($post);

            $body = 'Hartelijk dank voor uw aanmelding. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw aanmelding en betaalinformatie.';
            if (Setting::get('organisation') === 'Stichting Bijzondere Koorprojecten')
            {
                $body = 'Hartelijk dank voor je aanmelding. Je ontvangt binnen enkele minuten een e-mail met een bevestiging van je aanmelding.';
            }

            $page = new Page(
                'Aanmelding verwerkt',
                $body
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
     * @throws Exception
     * @return bool
     */
    private function processRegistration(RequestParameters $post): bool
    {
        if ($post->isEmpty())
        {
            throw new IncompleteData('De aanmeldingsgegevens zijn niet goed aangekomen.');
        }

        $eventId = $post->getInt('event_id');

        /** @var Event|null $eventObj */
        $eventObj = Event::loadFromDatabase($eventId);
        if ($eventObj === null)
        {
            throw new Exception('Evenement niet gevonden!');
        }

        if (!$eventObj->openForRegistration)
        {
            $warning = 'De aanmelding voor dit evenement is helaas gesloten, u kunt zich niet meer aanmelden.';
            if (Setting::get('organisation') === 'Stichting Bijzondere Koorprojecten')
            {
                $warning = 'De aanmelding voor dit evenement is helaas gesloten, je kunt je niet meer aanmelden.';
            }
            throw new Exception($warning);
        }

        $errorFields = $this->checkForm($eventObj, $post);
        if (!empty($errorFields))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $errorFields) . '.';
            throw new IncompleteData($message);
        }

        $registrationTicketTypes = [];
        $ticketTypes = EventTicketType::loadByEvent($eventObj);
        foreach ($ticketTypes as $ticketType)
        {
            $registrationTicketTypes[$ticketType->id] = $post->getInt('tickettype-' . $ticketType->id);
        }

        assert($eventObj->id !== null);
        $registration = new Registration();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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
        $registration->participatedBefore = $post->getBool('participatedBefore');
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
        if ($registrationTotal <= 0)
        {
            throw new Exception('Het formulier is niet goed aangekomen.');
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
                /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
                $ott->orderId = $registration->id;
                /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
                $ott->tickettypeId = $ticketType->id;
                $ott->amount = $registrationTicketTypes[$ticketType->id];
                $result = $ott->save();
                if (!$result)
                {
                    throw new DatabaseError('Opslaan kaarttypen mislukt!');
                }
            }
        }

        return $registration->sendIntroductionMail($registrationTotal, $registrationTicketTypes);
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

    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $registration->delete();

        return new JsonResponse();
    }

    public function setApprovalStatus(QueryBits $queryBits, RequestParameters $post): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
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

    public function setIsPaid(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $registration->setIsPaid();

        return new JsonResponse();
    }
}
