<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\UserSession;
use function assert;

final class EditorSave extends \Cyndaron\Editor\EditorSave
{
    public const TYPE = 'mailform';

    public function __construct(
        private readonly RequestParameters $post,
        private readonly ImageExtractor    $imageExtractor,
        private readonly UserSession       $userSession,
        private readonly GenericRepository $repository,
    ) {
    }

    public function save(int|null $id): int
    {
        $mailform = $this->repository->fetchOrCreate(Mailform::class, $id);
        $mailform->name = $this->post->getHTML('titel');
        $mailform->email = $this->post->getEmail('email');
        $mailform->antiSpamAnswer = $this->post->getAlphaNum('antiSpamAnswer');
        $mailform->sendConfirmation = $this->post->getBool('sendConfirmation');
        $mailform->confirmationText = $this->imageExtractor->process($this->post->getHTML('artikel'));

        try
        {
            $this->repository->save($mailform);
            $this->userSession->addNotification('Mailformulier bewerkt.');
        }
        catch (\PDOException)
        {
            $this->userSession->addNotification('Opslaan mailformulier mislukt');
        }

        $this->returnUrl = '/pagemanager/mailform';

        assert($mailform->id !== null);
        return $mailform->id;
    }
}
