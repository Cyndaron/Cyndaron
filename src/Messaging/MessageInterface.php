<?php
declare(strict_types=1);

namespace Cyndaron\Messaging;

use DateTimeInterface;

interface MessageInterface
{
    public function getBody(): string;
    public function isRead(): bool;
    public function getUserName(): string;
    public function getUserLink(): string;
    public function getUserAvatar(): string;
    public function getDateTime(): DateTimeInterface|null;
}
