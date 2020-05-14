<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\User\UserLevel;
use Cyndaron\Util;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class MailformController extends Controller
{
    protected array $postRoutes = [
        'process' => ['level' => UserLevel::ANONYMOUS, 'function' => 'process'],
        'process-ldbf' => ['level' => UserLevel::ANONYMOUS, 'function' => 'processLDBF'],
    ];

    public function checkCSRFToken(string $token): bool
    {
        if (in_array($this->action, ['process', 'process-ldbf']))
        {
            return true;
        }

        return parent::checkCSRFToken($token);
    }

    /**
     * @throws Exception
     */
    public function process(): Response
    {
        $id = (int)Request::getVar(2);
        $form = Mailform::loadFromDatabase($id);

        try
        {
            $this->processHelper($form);
            $page = new Page('Formulier verstuurd', 'Het versturen is gelukt.');
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new Page('Formulier versturen mislukt', $e->getMessage());
            return new Response($page->render());
        }
    }

    public function processLDBF(): Response
    {
        try
        {
            $this->processLDBFHelper();

            $page = new Page('Formulier verstuurd', 'Het versturen is gelukt.');
            return new Response($page->render());
        }

        catch (Exception $e)
        {
            $page = new Page('Formulier versturen mislukt', $e->getMessage());
            return new Response($page->render());
        }
    }

    private function processLDBFHelper(): bool
    {
        if (Request::postIsEmpty())
        {
            throw new Exception('Ongeldig formulier.');
        }
        if (empty(Request::post('E-mailadres')))
        {
            throw new Exception('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        $mailForm = new MailFormLDBF();
        $mailForm->fillMailTemplate();
        $mailSent = $mailForm->sendMail();

        if (!$mailSent)
        {
            throw new Exception('Wegens een technisch probleem is het versturen van de e-mail niet gelukt.');
        }

        return true;
    }

    /**
     * @param Mailform $form
     * @return bool
     * @throws Exception
     */
    public function processHelper(Mailform $form): bool
    {
        if ($form === null || !$form->name)
        {
            throw new Exception('Ongeldig formulier.');
        }

        if ($form->sendConfirmation && empty(Request::post('E-mailadres')))
        {
            throw new Exception('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        if (strtolower(Request::post('antispam')) !== strtolower($form->antiSpamAnswer))
        {
            throw new Exception('U heeft de antispamvraag niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        $mailBody = '';
        foreach (Request::post() as $question => $answer)
        {
            if ($question !== 'antispam')
            {
                $mailBody .= $question . ': ' . strtr($answer, ['\\' => '']) . "\n";
            }
        }
        $recipient = $form->email;
        $subject = $form->name;
        $sender = Request::post('E-mailadres');

        $fromAddress = Util::getNoreplyAddress();
        $fromName = html_entity_decode(Setting::get('organisation') ?: Setting::get('siteName'));
        $extraHeaders = 'From: ' . $fromAddress;

        if ($sender)
        {
            $extraHeaders .= "\r\n" . 'Reply-To: ' . $sender;
        }

        if (mail($recipient, $subject, $mailBody, $extraHeaders, "-f$fromAddress"))
        {
            if ($form->sendConfirmation && $sender)
            {
                $extraHeaders = sprintf('From: %s <%s>', $fromName, $fromAddress);
                $extraHeaders .= "\r\n" . 'Reply-To: ' . $recipient;
                mail($sender, 'Ontvangstbevestiging', $form->confirmationText, $extraHeaders, "-f$fromAddress");
            }
            return true;
        }

        throw new Exception('Wegens een technisch probleem is het versturen niet gelukt.');
    }
}