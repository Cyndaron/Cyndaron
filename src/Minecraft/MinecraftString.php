<?php
namespace Cyndaron\Minecraft;

class MinecraftString
{
    public const MINECRAFT_COLOUR_CODES = [
        '0' => '#000000',
        '1' => '#0000AA',
        '2' => '#00AA00',
        '3' => '#00AAAA',
        '4' => '#AA0000',
        '5' => '#AA00AA',
        '6' => '#FFAA00',
        '7' => '#AAAAAA',
        '8' => '#555555',
        '9' => '#5555FF',
        'a' => '#55FF55',
        'b' => '#55FFFF',
        'c' => '#FF5555',
        'd' => '#FF55FF',
        'e' => '#FFFF55',
        'f' => '#FFFFFF',
    ];

    public const MINECRAFT_FORMATTING_CODES = [
        'l' => 'font-weight:bold;',
        'm' => 'text-decoration:line-through;',
        'n' => 'text-decoration: underline;',
        'o' => 'font-style: italic;',
    ];

    private string $source;
    private string $outputString;
    private int $paragraphSignLength;
    private string $ending = '';

    public function __construct(string $source)
    {
        $this->source = $source;
        $this->outputString = '';
        $this->paragraphSignLength = strlen('§');
    }

    public function toHtml(): string
    {
        preg_match_all('/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/u', $this->source, $brokenupstrings);
        foreach ($brokenupstrings as $results)
        {
            $this->ending = '';
            foreach ($results as $individual)
            {
                $this->processBlock($individual);
            }
        }

        return $this->outputString;
    }

    private function processBlock($block): void
    {
        $code = preg_split("/[&§][0-9a-z]/u", $block);
        // This can be null if the text to format is preceeded by multiple formatting codes,
        // e.g. one for colour and one for text decoration.
        $textAfterFormatting = $code[1] ?? null;
        preg_match("/[&§][0-9a-z]/u", $block, $prefix);
        // If set, this will be a value like "§a".
        $formattingCode = $prefix[0] ?? null;
        if ($formattingCode !== null)
        {
            $this->processFormattingCode($formattingCode, strlen($block));

            if ($textAfterFormatting !== null)
            {
                $this->outputString .= $code[1];
                if (strlen($block) > $this->paragraphSignLength + 1)
                {
                    $this->outputString .= $this->ending;
                    $this->ending = '';
                }
            }
        }
        else
        {
            $this->outputString .= $block;
        }
    }

    /**
     * @param string $code The formatting code, e.g. “§a".
     * @param int $blockLength
     */
    private function processFormattingCode(string $code, int $blockLength): void
    {
        $actualcode = substr($code, $this->paragraphSignLength);
        if (array_key_exists($actualcode, self::MINECRAFT_COLOUR_CODES))
        {
            $this->outputString .= sprintf('<span style="color:%s;">', self::MINECRAFT_COLOUR_CODES[$actualcode]);
            $this->ending .= '</span>';
        }
        elseif (array_key_exists($actualcode, self::MINECRAFT_FORMATTING_CODES))
        {
            if ($blockLength > $this->paragraphSignLength + 1)
            {
                $this->outputString .= sprintf('<span style="%s">', self::MINECRAFT_FORMATTING_CODES[$actualcode]);
                $this->ending = '</span>' . $this->ending;
            }
            else
            {
                $this->outputString .= $this->ending;
                $this->ending = '';
            }
        }
        elseif ($actualcode === 'r')
        {
            $this->outputString .= $this->ending;
            $this->ending = '';
        }
    }
}