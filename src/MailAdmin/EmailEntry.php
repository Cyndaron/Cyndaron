<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

interface EmailEntry
{
    public function getEmail(): string;
}
