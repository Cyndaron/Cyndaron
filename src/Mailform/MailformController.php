<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\DBAL\DatabaseError;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Mail\Mail;
use Cyndaron\Util\Mail as UtilMail;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function in_array;
use function strcasecmp;
use function strtr;

final class MailformController extends Controller
{
    protected array $postRoutes = [
        'process' => ['level' => UserLevel::ANONYMOUS, 'function' => 'process'],
        'process-ldbf' => ['level' => UserLevel::ANONYMOUS, 'function' => 'processLDBF'],
    ];
    protected array $apiPostRoutes = [
        'delete' => ['level' => UserLevel::ADMIN, 'function' => 'delete'],
    ];

    public function checkCSRFToken(string $token): bool
    {
        if (in_array($this->action, ['process', 'process-ldbf'], true))
        {
            return true;
        }

        return parent::checkCSRFToken($token);
    }

    /**
     * @param RequestParameters $post
     * @throws Exception
     * @return Response
     */
    public function process(QueryBits $queryBits, RequestParameters $post): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $form = Mailform::fetchById($id);

        try
        {
            if ($form === null)
            {
                throw new IncompleteData('Formulier niet gevonden!');
            }
            $this->processHelper($form, $post);
            $page = new SimplePage('Formulier verstuurd', 'Het versturen is gelukt.');
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Formulier versturen mislukt', $e->getMessage());
            return new Response($page->render());
        }
    }

    public function processLDBF(RequestParameters $post): Response
    {
        try
        {
            $this->processLDBFHelper($post);

            $page = new SimplePage('Formulier verstuurd', 'Het versturen is gelukt.');
            return new Response($page->render());
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Formulier versturen mislukt', $e->getMessage());
            return new Response($page->render());
        }
    }

    private function processLDBFHelper(RequestParameters $post): bool
    {
        if ($post->isEmpty())
        {
            throw new IncompleteData('Ongeldig formulier.');
        }
        if (empty($post->getEmail('E-mailadres')))
        {
            throw new IncompleteData('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        $mailForm = new MailFormLDBF();
        $mailForm->fillMailTemplate($post);
        $mailSent = $mailForm->sendMail($post->getEmail('E-mailadres'));

        if (!$mailSent)
        {
            throw new DatabaseError('Wegens een technisch probleem is het versturen van de e-mail niet gelukt.');
        }

        return true;
    }

    /**
     * @param Mailform $form
     * @param RequestParameters $post
     * @throws Exception
     * @return bool
     */
    public function processHelper(Mailform $form, RequestParameters $post): bool
    {
        if ($form->name === '')
        {
            throw new IncompleteData('Ongeldig formulier.');
        }

        if ($form->sendConfirmation && empty($post->getEmail('E-mailadres')))
        {
            throw new IncompleteData('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        if (strcasecmp($post->getAlphaNum('antispam'), $form->antiSpamAnswer) !== 0)
        {
            throw new IncompleteData('U heeft de antispamvraag niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        $mailBody = '';
        foreach ($post->getKeys() as $question)
        {
            if ($question !== 'antispam')
            {
                $answer = $post->getHTML($question);
                $mailBody .= $question . ': ' . strtr($answer, ['\\' => '']) . "\n";
            }
        }
        $recipient = $form->email;
        $subject = $form->name;
        $sender = $post->getEmail('E-mailadres');

        $mail = UtilMail::createMailWithDefaults(new Address($recipient), $subject, $mailBody);
        if ($sender !== '')
        {
            $mail->addReplyTo(new Address($sender));
        }
        $mailSent = $mail->send();

        if ($mailSent)
        {
            if ($form->sendConfirmation && $sender && $form->confirmationText !== null)
            {
                $mail = UtilMail::createMailWithDefaults(
                    new Address($sender),
                    'Ontvangstbevestiging',
                    $form->confirmationText
                );
                $mail->addReplyTo(new Address($recipient));
                $mail->send();
            }
            return true;
        }

        throw new DatabaseError('Wegens een technisch probleem is het versturen niet gelukt.');
    }

    /**
     * @throws Exception
     * @return JsonResponse
     *
     */
    public function delete(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $mailform = Mailform::fetchById($id);
        if ($mailform === null)
        {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $mailform->delete();
        return new JsonResponse();
    }
}
