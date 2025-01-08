<?php
namespace Cyndaron;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Connection;
use Cyndaron\Util\Error\BootFailure;
use Cyndaron\Routing\Kernel;
use Cyndaron\Util\Setting;
use ErrorException;
use RuntimeException;

use Symfony\Component\HttpFoundation\Request;
use function Safe\ini_set;
use function set_error_handler;
use function file_exists;
use const E_DEPRECATED;

final class WebBootstrapper
{
    private const SETTINGS_FILE =  __DIR__ . '/../config.php';

    public function boot(): void
    {
        try
        {
            $this->setErrorHandler();
            $request = Request::createFromGlobals();
            $this->setPhpConfig((bool)$request->server->get('HTTPS'));
            $this->processSettings();
            $this->handleRequest($request);
        }
        catch (RuntimeException $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * Turn errors into exceptions
     */
    private function setErrorHandler(): void
    {
        // @phpstan-ignore-next-line
        set_error_handler(static function(int $severity, string $message, string $file, int $line)
        {
            if ($severity !== E_DEPRECATED)
            {
                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        });
    }

    private function setPhpConfig(bool $https): void
    {
        // Prevent passing the session ID via URLs.
        ini_set('session.use_only_cookies', '1');
        // Prevent Javascript from reading cookie contents.
        ini_set('session.cookie_httponly', '1');
        // Prevent users from specifying their own session ID.
        ini_set('session.use_strict_mode', '1');
        // Ensure SameSite attribute is set on all cookies.
        ini_set('session.cookie_samesite', 'Lax');

        if ($https)
        {
            // Ensure all cookies are sent via HTTPS.
            ini_set('session.cookie_secure', '1');
        }
    }

    private function processSettings(): void
    {
        if (!file_exists(self::SETTINGS_FILE))
        {
            throw new BootFailure('Geen instellingenbestand gevonden!');
        }

        $pdo = $this->connectToDatabase();
        Setting::load($pdo);
    }

    private function handleRequest(Request $request): void
    {
        $route = new Kernel();
        $response = $route->handle($request);
        $response->send();
    }

    private function connectToDatabase(): Connection
    {
        $dbmethode = 'mysql';
        $dbuser = 'root';
        $dbpass = '';
        $dbplek = 'localhost';
        $dbnaam = 'cyndaron';

        /** @noinspection PhpIncludeInspection */
        include self::SETTINGS_FILE;

        $connection = Connection::connect($dbmethode, $dbplek, $dbnaam, $dbuser, $dbpass);
        DBConnection::connect($connection);
        return $connection;
    }
}
