<?php
declare(strict_types=1);

namespace Cyndaron\Gopher\Response;

use Cyndaron\Gopher\MenuEntry;

class ListingResponse implements ResponseInterface
{
    use SendNonStreamTrait;

    /** @var MenuEntry[] */
    private array $entries;

    public function __construct(MenuEntry ...$entries)
    {
        $this->entries = $entries;
    }

    public function encode(): string
    {
        $response = '';
        foreach ($this->entries as $entry)
        {
            $response .= $entry->encode();
        }
        $response .= ".\r\n";

        return $response;
    }
}
