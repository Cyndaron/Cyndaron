<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Exception;

class RegisterController extends Controller
{
    protected $postRoutes = [
        'add' => ['level' => UserLevel::ANONYMOUS, 'function' => 'add'],
    ];

    protected function routePost()
    {
        $id = intval(Request::getVar(2));
        /** @var Registration $registration */
        $registration = Registration::loadFromDatabase($id);

        switch ($this->action)
        {
            case 'setIsPaid':
                $registration->setIsPaid();
                break;
            case 'setApprovalStatus':
                $status = (int)Request::post('status');
                if ($status >= Registration::APPROVAL_UNDECIDED && $status <= Registration::APPROVAL_DISAPPROVED)
                {
                    $registration->approvalStatus = $status;
                    $registration->save();
                }
                break;
            case 'delete':
                $registration->delete();
                break;
        }
    }

    protected function add()
    {
        $eventId = intval(Request::post('event_id'));
        try
        {
            $this->processOrder($eventId);

            $page = new Page(
                'Inschrijving verwerkt',
                'Hartelijk dank voor uw inschrijving. U ontvangt binnen enkele minuten een e-mail met een bevestiging van uw inschrijving.'
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
            throw new Exception($msg . 'Opslaan inschrijving mislukt!');
        }

        $order->sendConfirmationMail();
    }

    private function checkForm(Event $event)
    {
        $errorFields = [];
        if (strcasecmp(Request::post('antispam'),$event->getAntispam()) !== 0)
        {
            $errorFields[] = 'Antispam';
        }

        if (strlen(Request::post('lastName')) === 0)
        {
            $errorFields[] = 'Achternaam';
        }

        if (strlen(Request::post('initials')) === 0)
        {
            $errorFields[] = 'Voorletters';
        }

        if (strlen(Request::post('email')) === 0)
        {
            $errorFields[] = 'E-mailadres';
        }

        return $errorFields;
    }
}