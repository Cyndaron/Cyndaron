<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'mailform';

    protected function prepare(RequestParameters $post, Request $request): void
    {
        $mailform = new Mailform($this->id);
        $mailform->loadIfIdIsSet();
        $mailform->name = $post->getHTML('titel');
        $mailform->email = $post->getEmail('email');
        $mailform->antiSpamAnswer = $post->getAlphaNum('antiSpamAnswer');
        $mailform->sendConfirmation = $post->getBool('sendConfirmation');
        $mailform->confirmationText = $this->imageExtractor->process($post->getHTML('artikel'));

        if ($mailform->save())
        {
            User::addNotification('Mailformulier bewerkt.');
        }
        else
        {
            User::addNotification('Opslaan mailformulier mislukt');
        }

        $this->returnUrl = '/pagemanager/mailform';
    }
}
