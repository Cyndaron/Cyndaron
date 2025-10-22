<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Util\KnownShortCodes;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\TemplateRenderer;
use Exception;
use Safe\Exceptions\PcreException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function assert;
use function implode;
use function min;
use function strcasecmp;
use function file_exists;
use function html_entity_decode;

final class RegistrationController
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly PageRenderer $pageRenderer,
        private readonly RegistrationRepository $registrationRepository,
        private readonly RegistrationTicketTypeRepository $registrationTicketTypeRepository,
        private readonly EventRepository $eventRepository,
        private readonly EventTicketTypeRepository $eventTicketTypeRepository,
    ) {
    }

    #[RouteAttribute('add', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function add(RequestParameters $post, MailFactory $mailFactory): Response
    {
        try
        {
            $eventId = $post->getInt('event_id');
            /** @var Event|null $eventObj */
            $eventObj = $this->eventRepository->fetchById($eventId);
            if ($eventObj === null)
            {
                throw new Exception('Evenement niet gevonden!');
            }

            $this->processRegistration($post, $mailFactory);

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
     * @param MailFactory $mailFactory
     * @throws PcreException
     * @return bool
     */
    private function processRegistration(RequestParameters $post, MailFactory $mailFactory): bool
    {
        if ($post->isEmpty())
        {
            throw new IncompleteData('De aanmeldingsgegevens zijn niet goed aangekomen.');
        }

        $eventId = $post->getInt('event_id');

        /** @var Event|null $eventObj */
        $eventObj = $this->eventRepository->fetchById($eventId);
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
        $ticketTypes = $this->eventTicketTypeRepository->loadByEvent($eventObj);
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            $registrationTicketTypes[$ticketType->id] = $post->getInt('tickettype-' . $ticketType->id);
        }

        assert($eventObj->id !== null);
        $registration = new Registration();
        $registration->event = $eventObj;
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
        $registration->masterclass = $post->getBool('masterclass');
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
        $registration->approvalStatus = $eventObj->requireApproval ? RegistrationApprovalStatus::UNDECIDED : RegistrationApprovalStatus::APPROVED;

        try
        {
            $this->registrationRepository->save($registration);
        }
        catch (\Throwable)
        {
            throw new DatabaseError('Opslaan aanmelding mislukt!');
        }

        assert($registration->id !== null);
        foreach ($ticketTypes as $ticketType)
        {
            assert($ticketType->id !== null);
            if ($registrationTicketTypes[$ticketType->id] > 0)
            {
                $rtt = new RegistrationTicketType();
                $rtt->registration = $registration;
                $rtt->ticketType = $ticketType;
                $rtt->amount = $registrationTicketTypes[$ticketType->id];
                $this->registrationTicketTypeRepository->save($rtt);
            }
        }

        $registrationTotal = $registration->calculateTotal($this->registrationTicketTypeRepository);
        if ($registrationTotal === 0.00)
        {
            $registration->isPaid = true;
            $this->registrationRepository->save($registration);
        }

        return $this->sendIntroductionMail($registration, $mailFactory, $registrationTotal, $registrationTicketTypes, $this->templateRenderer);
    }

    /**
     * @param Registration $registration
     * @param MailFactory $mailFactory
     * @param float $registrationTotal
     * @param array<int, int> $registrationTicketTypes
     * @param TemplateRenderer $templateRenderer
     * @return bool
     */
    private function sendIntroductionMail(Registration $registration, MailFactory $mailFactory, float $registrationTotal, array $registrationTicketTypes, TemplateRenderer $templateRenderer): bool
    {
        $ticketTypes = $this->eventTicketTypeRepository->loadByEvent($registration->event);
        $lunchText = ($registration->lunch) ? $registration->lunchType : 'Geen';
        $extraFields = [
            'Geboortejaar' => $registration->birthYear,
            'Straatnaam en huisnummer' => "$registration->street $registration->houseNumber $registration->houseNumberAddition",
            'Postcode' => $registration->postcode,
            'Woonplaats' => $registration->city,
            'Opmerkingen' => $registration->comments,
        ];

        $templateFile = 'Registration/ConfirmationMail';
        $shortCode = Setting::get(BuiltinSetting::SHORT_CODE);
        if ($shortCode === KnownShortCodes::VOV)
        {
            $templateFile = 'Registration/ConfirmationMailVOV';
            if (file_exists(__DIR__ . '/templates/ConfirmationMailVOV-' . $registration->event->id . '.blade.php'))
            {
                $templateFile = 'Registration/ConfirmationMailVOV-' . $registration->event->id;
            }
        }

        $args = ['registration' => $registration, 'event' => $registration->event, 'registrationTotal' => $registrationTotal, 'ticketTypes' => $ticketTypes, 'registrationTicketTypes' => $registrationTicketTypes, 'lunchText' => $lunchText, 'extraFields' => $extraFields];
        $text = $templateRenderer->render($templateFile, $args);
        // We're sending a plaintext mail, so avoid displaying html entities.
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $mail = $mailFactory->createMailWithDefaults(new Address($registration->email), 'Inschrijving ' . $registration->event->name, $text);
        return $mail->send();
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
        $this->registrationRepository->deleteById($id);

        return new JsonResponse();
    }

    #[RouteAttribute('setApprovalStatus', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function setApprovalStatus(QueryBits $queryBits, RequestParameters $post, MailFactory $mailFactory): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = $this->registrationRepository->fetchById($id);
        $status = RegistrationApprovalStatus::tryFrom($post->getInt('status'));
        switch ($status)
        {
            case RegistrationApprovalStatus::APPROVED:
                $registration->approvalStatus = RegistrationApprovalStatus::APPROVED;
                $this->registrationRepository->save($registration);

                $event = $registration->event;

                $text = '';

                $mail = $mailFactory->createMailWithDefaults(
                    new Address($registration->email),
                    'Aanmelding ' . $event->name . ' goedgekeurd',
                    $text
                );
                $mail->send();
                break;
            case RegistrationApprovalStatus::DISAPPROVED:
                $registration->approvalStatus = RegistrationApprovalStatus::DISAPPROVED;
                $this->registrationRepository->save($registration);

                $event = $registration->event;

                if ($event->requireApproval)
                {
                    $text = '';
                }
                else
                {
                    $text = 'Uw aanmelding is geannuleerd. Eventuele betalingen zullen worden teruggestort.';
                }

                $mail = $mailFactory->createMailWithDefaults(
                    new Address($registration->email),
                    'Aanmelding ' . $event->name,
                    $text
                );
                $mail->send();
                break;
        }

        return new JsonResponse();
    }

    #[RouteAttribute('setIsPaid', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function setIsPaid(QueryBits $queryBits, MailFactory $mailFactory): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        /** @var Registration $registration */
        $registration = $this->registrationRepository->fetchById($id);
        $registration->isPaid = true;
        $this->registrationRepository->save($registration);

        $organisation = Setting::get(BuiltinSetting::ORGANISATION);
        $text = "Hartelijk dank voor uw inschrijving bij $organisation. Wij hebben uw betaling in goede orde ontvangen.\n";
        if (Setting::get(BuiltinSetting::SHORT_CODE) !== KnownShortCodes::VOV)
        {
            $text .= 'Eventueel bestelde kaarten voor vrienden en familie zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        $mail = $mailFactory->createMailWithDefaults(
            new Address($registration->email),
            'Betalingsbevestiging ' . $registration->event->name,
            $text
        );
        $mail->send();

        return new JsonResponse();
    }
}
