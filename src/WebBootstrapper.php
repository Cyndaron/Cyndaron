<?php
namespace Cyndaron;

use Cyndaron\Error\BootFailure;
use Cyndaron\Routing\Router;
use ErrorException;
use RuntimeException;

use Symfony\Component\HttpFoundation\Request;
use function Safe\ini_set;
use function set_error_handler;
use function file_exists;

final class WebBootstrapper
{
    public function boot(): void
    {
        try
        {
            $this->setErrorHandler();
            $this->registerAutoloaders();
            $this->setPhpConfig();
            $this->processSettings();
            $this->handleRequest();
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
        set_error_handler(static function(int $severity, string $message, string $file, int $line)
        {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    private function setPhpConfig(): void
    {
        // Prevent passing the session ID via URLs.
        ini_set('session.use_only_cookies', true);
        // Prevent Javascript from reading cookie contents.
        ini_set('session.cookie_httponly', true);
        // Ensure all cookies are sent via HTTPS.
        ini_set('session.cookie_secure', true);
        // Prevent users from specifying their own session ID.
        ini_set('session.use_strict_mode', true);
        // Moved from DBConnection. TODO: check if really needed.
        ini_set('memory_limit', '96M');
    }

    private function registerAutoloaders(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    private function processSettings(): void
    {
        $settingsFile = $this->getSettingsFile();
        if ($settingsFile === null)
        {
            throw new BootFailure('Geen instellingenbestand gevonden!');
        }

        $this->connectToDatabase($settingsFile);
    }

    private function handleRequest(): void
    {
        $route = new Router();
        $response = $route->handle(Request::createFromGlobals());
        $response->send();
    }

    /**
     * @param string $settingsFile
     */
    private function connectToDatabase(string $settingsFile): void
    {
        $dbmethode = 'mysql';
        $dbuser = 'root';
        $dbpass = '';
        $dbplek = 'localhost';
        $dbnaam = 'cyndaron';

        /** @noinspection PhpIncludeInspection */
        include $settingsFile;

        DBConnection::connect($dbmethode, $dbplek, $dbnaam, $dbuser, $dbpass);
    }

    private function getSettingsFile(): ?string
    {
        static $settingsFiles = [
            __DIR__ . '/../config.php',
            __DIR__ . '/../instellingen.php',
        ];

        foreach ($settingsFiles as $settingsFile)
        {
            if (file_exists($settingsFile))
            {
                return $settingsFile;
            }
        }

        return null;
    }
}
