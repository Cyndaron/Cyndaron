<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Cyndaron\User\UserSession;
use function assert;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'mailform';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly ImageExtractor $imageExtractor,
    ) {
    }

    public function save(int|null $id): int
    {
        $mailform = new Mailform($id);
        $mailform->loadIfIdIsSet();
        $mailform->name = $this->post->getHTML('titel');
        $mailform->email = $this->post->getEmail('email');
        $mailform->antiSpamAnswer = $this->post->getAlphaNum('antiSpamAnswer');
        $mailform->sendConfirmation = $this->post->getBool('sendConfirmation');
        $mailform->confirmationText = $this->imageExtractor->process($this->post->getHTML('artikel'));

        if ($mailform->save())
        {
            UserSession::addNotification('Mailformulier bewerkt.');
        }
        else
        {
            UserSession::addNotification('Opslaan mailformulier mislukt');
        }

        $this->returnUrl = '/pagemanager/mailform';

        assert($mailform->id !== null);
        return $mailform->id;
    }
}
