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

    public function __construct(string $source)
    {
        $this->source = $source;
    }

    public function toHtml()
    {
        $paragraphSignLength = strlen('§');

        preg_match_all('/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/u', $this->source, $brokenupstrings);
        $returnstring = '';
        foreach ($brokenupstrings as $results)
        {
            $ending = '';
            foreach ($results as $individual)
            {
                $code = preg_split("/[&§][0-9a-z]/u", $individual);
                preg_match("/[&§][0-9a-z]/u", $individual, $prefix);
                if (isset($prefix[0]))
                {
                    $actualcode = substr($prefix[0], $paragraphSignLength);
                    if (array_key_exists($actualcode, self::MINECRAFT_COLOUR_CODES))
                    {
                        $returnstring .= sprintf('<span style="color:%s">', self::MINECRAFT_COLOUR_CODES[$actualcode]);
                        $ending .= '</span>';
                    }
                    elseif (array_key_exists($actualcode, self::MINECRAFT_FORMATTING_CODES))
                    {
                        if (strlen($individual) > $paragraphSignLength + 1)
                        {
                            $returnstring .= sprintf('<span style="%s">', self::MINECRAFT_FORMATTING_CODES[$actualcode]);
                            $ending = '</span>' . $ending;
                        }
                        else
                        {
                            $returnstring .= $ending;
                            $ending = '';
                        }
                    }
                    elseif ($actualcode === 'r')
                    {
                        $returnstring .= $ending;
                        $ending = '';
                    }

                    if (isset($code[1]))
                    {
                        $returnstring .= $code[1];
                        if (isset($ending) && strlen($individual) > $paragraphSignLength + 1)
                        {
                            $returnstring .= $ending;
                            $ending = '';
                        }
                    }
                }
                else
                {
                    $returnstring .= $individual;
                }
            }
        }

        return $returnstring;
    }
}