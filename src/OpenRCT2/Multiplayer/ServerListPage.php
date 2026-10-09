<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Multiplayer;

use Cyndaron\CyndaronInfo;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Translation\Translator;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\SettingsRepository;
use IntlDateFormatter;
use Symfony\Component\HttpFoundation\Response;
use function Safe\file_get_contents;
use function array_key_exists;
use function in_array;
use function is_array;
use function json_decode;
use function stream_context_create;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function assert;
use function floor;
use function sprintf;
use function usort;

final class ServerListPage
{
    private const COLOR_TOKENS = [
        'BABYBLUE',
        'BLACK',
        'CELADON',
        'GREEN',
        'GREY',
        'LIGHTPINK',
        'PALEGOLD',
        'PALELAVENDER',
        'PALESILVER',
        'PEARLAQUA',
        'RED',
        'TOPAZ',
        'WHITE',
        'YELLOW',
    ];

    private readonly IntlDateFormatter $formatter;

    private readonly string $masterServerUrl;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly Translator $t,
        SettingsRepository $sr
    ) {
        $this->masterServerUrl = $sr->get('openrct2_masterServerUrl');
        $language = $sr->get(BuiltinSetting::LANGUAGE) ?: 'nl';
        $this->formatter = new IntlDateFormatter($language);
        $this->formatter->setPattern('MMMM');
    }

    /**
     * @return array{status?: string, servers?: list<array{description: string, maxPlayers: int, name: string, players: int, requiresPassword: bool, version: string, gameInfo?: array{ month: int}}>}
     */
    private function fetchJson(): array
    {
        $options  = [
            'http' => [
                'user_agent' => CyndaronInfo::PRODUCT_NAME . '/' . CyndaronInfo::ENGINE_VERSION,
                'header' => "Accept: application/json"
            ],
        ];
        $context  = stream_context_create($options);
        $ret = json_decode(file_get_contents($this->masterServerUrl, context: $context), true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($ret))
        {
            return ['status' => 'failed'];
        }
        return $ret;
    }

    private function decodeOpenRCT2String(string $string): string
    {
        $length = strlen($string);
        if ($length === 0)
        {
            return '';
        }

        $position = 0;
        $ret = '';
        $inColourCode = false;

        while ($position < $length)
        {
            $currentChar = substr($string, $position, 1);
            if ($currentChar === '{')
            {
                $endPos = strpos($string, '}', $position);
                if ($endPos !== false)
                {
                    $code = substr($string, $position + 1, $endPos - $position - 1);
                    // Tokens other than colour (e.g. outline, inline sprites) get ignored
                    if (in_array($code, self::COLOR_TOKENS, true))
                    {
                        if ($inColourCode)
                        {
                            $ret .= '</span>';
                        }

                        $inColourCode = true;
                        $colourClass = 'rct-' . strtolower($code);
                        $ret .= '<span class="' . $colourClass . '">';

                    }
                    $position = $endPos + 1;
                }
            }
            else
            {
                $ret .= $currentChar;
                $position++;
            }
        }

        if ($inColourCode)
        {
            $ret .= '</span>';
        }

        return $ret;
    }

    private function formatDate(int $months): string
    {
        $year = (int)floor($months / 8) + 1;
        $month = ($months % 8) + 3;
        $date = \DateTimeImmutable::createFromFormat('n', (string)$month);
        assert($date !== false);
        $monthFormatted = $this->formatter->format($date) ?: '???';
        return sprintf($this->t->get('openrct2.yearmonth'), $monthFormatted, $year);

    }

    /**
     * @return Server[]
     */
    private function getServers(): array
    {
        $json = $this->fetchJson();
        $status = $json['status'] ?? '';
        if ($status !== 'ok' || !array_key_exists('servers', $json))
        {
            return [];
        }

        $ret = [];
        foreach ($json['servers'] as $jsonServer)
        {
            $gameInfo = $jsonServer['gameInfo'] ?? ['month' => 0];

            $ret[] = new Server(
                $this->decodeOpenRCT2String($jsonServer['name']),
                $this->decodeOpenRCT2String($jsonServer['description']),
                $gameInfo['month'],
                $this->formatDate($gameInfo['month']),
                $jsonServer['players'],
                $jsonServer['maxPlayers'],
                $jsonServer['version'],
                $jsonServer['requiresPassword']
            );
        }

        usort($ret, static function(Server $a, Server $b)
        {
            $cmp1 = $b->version <=> $a->version;
            if ($cmp1 !== 0)
            {
                return $cmp1;
            }

            return $a->name <=> $b->name;
        });

        return $ret;
    }

    #[RouteAttribute('serverlist', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function show(): Response
    {
        $page = new Page();
        $page->title = $this->t->get('openrct2.multiplayer.serverlist');
        $page->template = 'OpenRCT2/Multiplayer/ServerListPage';
        $page->addTemplateVar('servers', $this->getServers());
        $page->addCss('/src/OpenRCT2/Multiplayer/css/serverlist.css');
        return $this->pageRenderer->renderResponse($page);
    }
}
