<?php
declare(strict_types=1);

namespace Cyndaron;

use Cyndaron\Base\CyndaronConfig;
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
            $config = $this->processSettings();
            $this->handleRequest($request, $config);
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

    private function processSettings(): CyndaronConfig
    {
        if (!file_exists(self::SETTINGS_FILE))
        {
            throw new BootFailure('Geen instellingenbestand gevonden!');
        }

        /** @var CyndaronConfig $config */
        $config = require self::SETTINGS_FILE;
        $pdo = $this->connectToDatabase($config);
        Setting::load($pdo);
        return $config;
    }

    private function handleRequest(Request $request, CyndaronConfig $config): void
    {
        $route = new Kernel();
        $response = $route->handle($request, $config);
        $response->send();
    }

    private function connectToDatabase(CyndaronConfig $config): Connection
    {
        $connection = Connection::create('mysql', $config->databaseHost, $config->databaseName, $config->databaseUser, $config->databasePassword);
        DBConnection::connect($connection);
        return $connection;
    }
}
