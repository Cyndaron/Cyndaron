<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\User\UserLevel;
use Cyndaron\VerwerkMailformulierPaginaLDBF;
use Exception;

class MailformController extends Controller
{
    protected $minLevelPost = UserLevel::ANONYMOUS;

    protected function routePost()
    {
        $id = (int)Request::getVar(2);
        try
        {
            if ($this->action === 'process-ldbf')
                new VerwerkMailformulierPaginaLDBF();
            else
                $this->process($id);
        }
        catch (Exception $e)
        {
            $page = new Page('Formulier versturen mislukt', $e->getMessage());
            $page->render();
        }
        $page = new Page('Formulier verstuurd', 'Het versturen is gelukt.');
        $page->render();

    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    private function process(int $id)
    {
        $form = Mailform::loadFromDatabase($id)->asArray();

        if ($form['naam'])
        {
            if ($form['stuur_bevestiging'] == true && empty(Request::post('E-mailadres')))
            {
                throw new Exception('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
            }
            elseif (strtolower(Request::post('antispam')) == strtolower($form['antispamantwoord']))
            {
                $mailBody = '';
                foreach (Request::post() as $question => $answer)
                {
                    if ($question !== 'antispam')
                    {
                        $mailBody .= $question . ': ' . strtr($answer, ['\\' => '']) . "\n";
                    }
                }
                $recipient = $form['mailadres'];
                $subject = $form['naam'];
                $sender = Request::post('E-mailadres');

                $server = str_replace("www.", "", $_SERVER['HTTP_HOST']);
                $server = str_replace("http://", "", $server);
                $server = str_replace("https://", "", $server);
                $server = str_replace("/", "", $server);
                $fromAddress = "noreply@$server";
                $fromName = html_entity_decode(Setting::get('siteName'));
                $extraHeaders = 'From: ' . $fromAddress;

                if ($sender)
                {
                    $extraHeaders .= "\r\n" . 'Reply-To: ' . $sender;
                }

                if (mail($recipient, $subject, $mailBody, $extraHeaders, "-f$fromAddress"))
                {
                    if ($form['stuur_bevestiging'] == true && $sender)
                    {
                        $extraHeaders = sprintf('From: %s <%s>', $fromName, $fromAddress);
                        $extraHeaders .= "\r\n" . 'Reply-To: ' . $recipient;
                        mail($sender, 'Ontvangstbevestiging', $form['tekst_bevestiging'], $extraHeaders, "-f$fromAddress");
                    }
                    return true;
                }
                else
                {
                    throw new Exception('Wegens een technisch probleem is het versturen niet gelukt.');
                }
            }
            else
            {
                throw new Exception('U heeft de antispamvraag niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
            }
        }
        else
        {
            throw new Exception('Ongeldig formulier.');
        }
    }
}