<?php /** @noinspection PhpMissingBreakStatementInspection */

namespace Cyndaron\Minecraft;

class Util
{
    public static function mineToWeb($minetext): string
    {
        preg_match_all('/[^§&]*[^§&]|[§&][0-9a-z][^§&]*/', $minetext, $brokenupstrings);
        $returnstring = '';
        foreach ($brokenupstrings as $results)
        {
            $ending = '';
            foreach ($results as $individual)
            {
                $code = preg_split("/[&§][0-9a-z]/", $individual);
                preg_match("/[&§][0-9a-z]/", $individual, $prefix);
                if (isset($prefix[0]))
                {
                    $actualcode = substr($prefix[0], 1);
                    switch ($actualcode)
                    {
                        case '1':
                            $returnstring .= '<span style="color:#0000AA;">';
                            $ending .= '</span>';
                            break;
                        case '2':
                            $returnstring .= '<span style="color:#00AA00;">';
                            $ending .= '</span>';
                            break;
                        case '3':
                            $returnstring .= '<span style="color:#00AAAA;">';
                            $ending .= '</span>';
                            break;
                        case '4':
                            $returnstring .= '<span style="color:#AA0000;">';
                            $ending .= '</span>';
                            break;
                        case '5':
                            $returnstring .= '<span style="color:#AA00AA;">';
                            $ending .= '</span>';
                            break;
                        case '6':
                            $returnstring .= '<span style="color:#FFAA00;">';
                            $ending .= '</span>';
                            break;
                        case '7':
                            $returnstring .= '<span style="color:#AAAAAA;">';
                            $ending .= '</span>';
                            break;
                        case '8':
                            $returnstring .= '<span style="color:#555555;">';
                            $ending .= '</span>';
                            break;
                        case '9':
                            $returnstring .= '<span style="color:#5555FF;">';
                            $ending .= '</span>';
                            break;
                        case 'a':
                            $returnstring .= '<span style="color:#55FF55;">';
                            $ending .= '</span>';
                            break;
                        case 'b':
                            $returnstring .= '<span style="color:#55FFFF;">';
                            $ending .= '</span>';
                            break;
                        case 'c':
                            $returnstring .= '<span style="color:#FF5555;">';
                            $ending .= '</span>';
                            break;
                        case 'd':
                            $returnstring .= '<span style="color:#FF55FF;">';
                            $ending .= '</span>';
                            break;
                        case 'e':
                            $returnstring .= '<span style="color:#FFFF55;">';
                            $ending .= '</span>';
                            break;
                        case 'f':
                            $returnstring .= '<span style="color:#FFFFFF;">';
                            $ending .= '</span>';
                            break;
                        case 'l':
                            if (strlen($individual) > 2)
                            {
                                $returnstring .= '<span style="font-weight:bold;">';
                                $ending = "</span>" . $ending;
                                break;
                            }
                        // fallthrough
                        case 'm':
                            if (strlen($individual) > 2)
                            {
                                $returnstring .= '<span style=" text-decoration:line-through;">';
                                $ending = '</span>' . $ending;
                                break;
                            }
                        // fallthrough
                        case 'n':
                            if (strlen($individual) > 2)
                            {
                                $returnstring .= '<span style="text-decoration: underline;">';
                                $ending = '</span>' . $ending;
                                break;
                            }
                        // fallthrough
                        case 'o':
                            if (strlen($individual) > 2)
                            {
                                $returnstring .= '<span style="font-style: italic;">';
                                $ending = '</span>' . $ending;
                                break;
                            }
                        // fallthrough
                        case 'r':
                            $returnstring .= $ending;
                            $ending = '';
                            break;
                    }
                    if (isset($code[1]))
                    {
                        $returnstring .= $code[1];
                        if (isset($ending) && strlen($individual) > 2)
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
