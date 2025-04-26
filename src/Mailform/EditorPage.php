<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use function assert;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'mailform';
    public const SAVE_URL = '/editor/mailform/%s';

    public string $template = '';

    public function __construct(private readonly MailformRepository $mailformRepository)
    {
    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $this->model = $this->mailformRepository->fetchById($this->id);
            assert($this->model !== null);
            $this->content = $this->model->confirmationText ?? '';
            $this->contentTitle = $this->model->name;
        }
    }
}
