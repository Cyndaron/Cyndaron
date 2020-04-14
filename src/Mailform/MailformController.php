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

class MailformController extends Controller
{
    protected int $minLevelPost = UserLevel::ANONYMOUS;

    public function checkCSRFToken(string $token): void
    {
        if (!in_array($this->action, ['process', 'process-ldbf']))
        {
            parent::checkCSRFToken($token);
        }
    }

    protected function routePost()
    {
        $id = (int)Request::getVar(2);
        try
        {
            if ($this->action === 'process-ldbf')
            {
                new VerwerkMailFormulierPaginaLDBF();
            }
            else
            {
                $this->process($id);
                $page = new Page('Formulier verstuurd', 'Het versturen is gelukt.');
                $page->render();
            }
        }
        catch (Exception $e)
        {
            $page = new Page('Formulier versturen mislukt', $e->getMessage());
            $page->render();
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    private function process(int $id)
    {
        $form = Mailform::loadFromDatabase($id);
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