<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\User\UserLevel;
use Exception;

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

    protected function add()
    {
        $eventId = (int)Request::post('event_id');
        try
        {
            $this->processOrder($eventId);

            $page = new Page(
                'Aanmelding verwerkt',
                'Hartelijk dank voor je aanmelding. Je ontvangt binnen enkele minuten een e-mail met een bevestiging van je aanmelding.'
            );
            $page->renderAndEcho();
        }
        catch (Exception $e)
        {
            $page = new Page('Fout bij verwerken aanmelding', $e->getMessage());
            $page->renderAndEcho();
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
            throw new Exception('De aanmeldingsgegevens zijn niet goed aangekomen.');
        }

        /** @var Event $eventObj */
        $eventObj = Event::loadFromDatabase($eventId);

        if (!$eventObj->openForRegistration)
        {
            throw new Exception('De aanmeldingen voor dit evenement is helaas gesloten, je kunt je niet meer aanmelden.');
        }

        $errorFields = $this->checkForm($eventObj);
        if (!empty($errorFields))
        {
            $message = 'De volgende velden zijn niet goed ingevuld of niet goed aangekomen: ';
            $message .= implode(', ', $errorFields) . '.';
            throw new Exception($message);
        }

        $order = new Registration();
        $order->eventId = $eventObj->id;
        $order->lastName = Request::post('lastName');
        $order->initials = Request::post('initials');
        $order->vocalRange = Request::post('vocalRange');
        $order->email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $order->phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
        $order->city = Request::post('city');
        $order->currentChoir = filter_input(INPUT_POST, 'currentChoir', FILTER_SANITIZE_STRING);
        $order->choirExperience = (int)filter_input(INPUT_POST, 'choirExperience', FILTER_SANITIZE_NUMBER_INT);
        $order->performedBefore = (bool)filter_input(INPUT_POST, 'performedBefore', FILTER_VALIDATE_BOOLEAN);
        $order->comments = Request::post('comments');

        $result = $order->save();
        if ($result === false)
        {
            $msg = var_export(DBConnection::errorInfo(), true);
            throw new Exception($msg . 'Opslaan aanmelding mislukt!');
        }

        $order->sendConfirmationMail();
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

    public function delete(): JSONResponse
    {
        $id = (int)Request::getVar(2);
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $registration->delete();

        return new JSONResponse();
    }

    public function setApprovalStatus(): JSONResponse
    {
        $id = (int)Request::getVar(2);
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $status = (int)Request::post('status');
        switch ($status)
        {
            case Registration::APPROVAL_APPROVED:
                $registration->setApproved();
                break;
            case Registration::APPROVAL_DISAPPROVED:
                $registration->setDisapproved();
                break;
        }

        return new JSONResponse();
    }

    public function setIsPaid(): JSONResponse
    {
        $id = (int)Request::getVar(2);
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);
        $registration->setIsPaid();

        return new JSONResponse();
    }
}