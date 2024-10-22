<?php
declare(strict_types=1);

namespace Cyndaron\Gopher;

class MenuEntryFactory
{
    public function __construct(
        public readonly string $host,
        public readonly string $subDomain = '',
        public readonly int $port = 70
    ) {
    }

    private function getSubdomain(): string
    {
        return $this->subDomain ? ('/' . $this->subDomain) : '';
    }

    public function createEntry(EntryType $type, string $title, string $location): MenuEntry
    {
        return new MenuEntry($type, $title, $location, $this->host, $this->port);
    }

    public function createDirectoryEntry(string $link, string|null $description = null): MenuEntry
    {
        return new MenuEntry(
            EntryType::Directory,
            $description ?? $link,
            $this->getSubdomain() . $link,
            $this->host,
            $this->port
        );
    }

    public function createTitleEntry(string $message): MenuEntry
    {
        return new MenuEntry(EntryType::Information, $message, 'TITLE', '(NULL)', 0);
    }

    public function createInformationEntry(string $message): MenuEntry
    {
        return new MenuEntry(EntryType::Information, $message, 'fake', '(NULL)', 0);
    }

    public function createHtmlFileEntry(string $link, string|null $description = null): MenuEntry
    {
        return new MenuEntry(EntryType::HTMLFile, $description ?? $link, $link, $this->host, $this->port);
    }

    public function createHtmlLinkEntry(string $link, string|null $description = null): MenuEntry
    {
        $location = "URL:{$link}";
        return new MenuEntry(EntryType::HTMLFile, $description ?? $link, $location, $this->host, $this->port);
    }
}
