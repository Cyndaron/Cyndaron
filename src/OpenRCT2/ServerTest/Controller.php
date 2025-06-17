<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\ServerTest;

use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use RuntimeException;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function Safe\preg_replace;
use function fsockopen;
use function fwrite;
use function unpack;
use function is_array;
use function fseek;
use function fread;
use function strpos;
use function substr;
use function fclose;

final class Controller
{
    // Packet length as a 16-bit big endian int, followed by a 32-bit big endian int for the command (9 = Server info)
    private const SERVERINFO_PAYLOAD = "\x00\x04\x00\x00\x00\x09";

    public function __construct(
        private readonly PageRenderer $renderer,
    ) {
    }

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function get(Request $request): Response
    {
        $page = new Page();
        $page->title = 'Server test';
        $page->template = 'OpenRCT2/ServerTest/FormPage';
        $ip = $request->headers->get('X-Forwarded-For') ?: $request->getClientIp();
        $page->addTemplateVar('currentIP', $ip);

        return $this->renderer->renderResponse($page);
    }

    #[RouteAttribute('', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function post(RequestParameters $post): Response
    {
        $ip = $post->getSimpleString('ip');
        /** @var string $ip */
        $ip = preg_replace('/[^A-F0-9:.]/i', '', $ip);
        $port = $post->getInt('port');

        $results = [];
        $message = '';

        try
        {
            $fp = fsockopen("tcp://{$ip}", $port, $errno, $errstr, 5);
            if ($fp === false)
            {
                throw new RuntimeException('Could not create socket!');
            }

            fwrite($fp, self::SERVERINFO_PAYLOAD);

            $lengthBE = fread($fp, 2) or throw new RuntimeException('Cannot read length!');
            $unpacked = unpack('n', $lengthBE);
            /** @var int<0, max> $length */
            $length = is_array($unpacked) ? ($unpacked[1] ?? 0) : 0;
            if ($length < 4)
            {
                throw new RuntimeException('Invalid packet size!');
            }
            // Contains the command ID, which we’re not interested in.
            fseek($fp, 4, SEEK_CUR);
            $rest = fread($fp, $length - 4) or throw new RuntimeException('Cannot read payload!');
            $nullPos = strpos($rest, "\x00");
            if ($nullPos !== false)
            {
                $rest = substr($rest, 0, $nullPos);
            }
            fclose($fp);

            $results = \Safe\json_decode($rest, true);
        }
        catch (JsonException $e)
        {
            $message = 'A connection was established, but the resulting data could not be parsed.';
        }
        catch (\Throwable)
        {
            $message = 'Could not connect to server';
        }

        $page = new Page();
        $page->title = 'Server test results';
        $page->template = 'OpenRCT2/ServerTest/ResultPage';

        return $this->renderer->renderResponse($page, [
            'results' => $results,
            'message' => $message,
        ]);
    }
}
