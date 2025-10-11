<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use function implode;
use function array_map;
use function str_replace;

final class OrderTotal
{
    public function __construct(
        /** @var string[] */
        public readonly array $orderLines,
        public readonly float $total,
    ) {
    }

    /**
     * @param OrderTicketType[] $orderTicketTypes
     */
    public static function fromOrderTicketTypes(array $orderTicketTypes): self
    {
        $orderLines = [];
        $total = 0.0;

        foreach ($orderTicketTypes as $orderTicketType)
        {
            if ($orderTicketType->amount === 0)
            {
                continue;
            }

            $orderLines[] = "{$orderTicketType->amount}× {$orderTicketType->type->name}";
            $total += ($orderTicketType->amount * $orderTicketType->type->price);
        }

        return new self($orderLines, $total);
    }

    public function asPlainText(): string
    {
        return implode("\r\n", $this->orderLines);
    }

    public function asListItems(): string
    {
        $listWrapper = fn (string $line) => str_replace(' ', '&nbsp;', "<li>{$line}</li>");
        return implode("\n", array_map($listWrapper, $this->orderLines));
    }
}
