<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use function assert;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public const TYPE = 'mailform';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly ImageExtractor $imageExtractor,
        private readonly UserSession $userSession,
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
            $this->userSession->addNotification('Mailformulier bewerkt.');
        }
        else
        {
            $this->userSession->addNotification('Opslaan mailformulier mislukt');
        }

        $this->returnUrl = '/pagemanager/mailform';

        assert($mailform->id !== null);
        return $mailform->id;
    }
}
