<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\DBAL\DatabaseError;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Util\MailFactory;
use Cyndaron\View\Template\TemplateRenderer;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function strcasecmp;
use function strtr;

final class MailformController
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly PageRenderer $pageRenderer,
        private readonly MailformRepository $mailformRepository,
    ) {
    }

    #[RouteAttribute('process', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function process(QueryBits $queryBits, RequestParameters $post, MailFactory $mailFactory): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $form = $this->mailformRepository->fetchById($id);

        try
        {
            if ($form === null)
            {
                throw new IncompleteData('Formulier niet gevonden!');
            }
            $this->processHelper($form, $mailFactory, $post);
            $page = new SimplePage('Formulier verstuurd', 'Het versturen is gelukt.');
            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Formulier versturen mislukt', $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
    }

    #[RouteAttribute('process-ldbf', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function processLDBF(RequestParameters $post, MailFormLDBF $mailForm): Response
    {
        try
        {
            $this->processLDBFHelper($post, $mailForm);

            $page = new SimplePage('Formulier verstuurd', 'Het versturen is gelukt.');
            return $this->pageRenderer->renderResponse($page);
        }
        catch (Exception $e)
        {
            $page = new SimplePage('Formulier versturen mislukt', $e->getMessage());
            return $this->pageRenderer->renderResponse($page);
        }
    }

    private function processLDBFHelper(RequestParameters $post, MailFormLDBF $mailForm): bool
    {
        if ($post->isEmpty())
        {
            throw new IncompleteData('Ongeldig formulier.');
        }
        if (empty($post->getEmail('E-mailadres')))
        {
            throw new IncompleteData('U heeft uw e-mailadres niet of niet goed ingevuld. Klik op Vorige om het te herstellen.');
        }

        $mailForm->fillMailTemplate($post, $this->templateRenderer);
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
    private function processHelper(Mailform $form, MailFactory $mailFactory, RequestParameters $post): bool
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

        $mail = $mailFactory->createMailWithDefaults(new Address($recipient), $subject, $mailBody);
        if ($sender !== '')
        {
            $mail->addReplyTo(new Address($sender));
        }
        $mailSent = $mail->send();

        if ($mailSent)
        {
            if ($form->sendConfirmation && $sender && $form->confirmationText !== null)
            {
                $mail = $mailFactory->createMailWithDefaults(
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

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $mailform = $this->mailformRepository->fetchById($id);
        if ($mailform === null)
        {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $repository->delete($mailform);
        return new JsonResponse();
    }
}
