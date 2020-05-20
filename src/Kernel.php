<?php
namespace Cyndaron;

use Cyndaron\Error\BootFailure;
use RuntimeException;

class Kernel
{
    public function __construct()
    {
    }

    public function boot(): void
    {
        try
        {
            $this->setPhpConfig();
            $this->registerAutoloaders();
            $this->processSettings();
            $this->handleRequest();
        }
        catch (RuntimeException $e)
        {
            echo $e->getMessage();
        }
    }

    protected function setPhpConfig(): void
    {
        // Prevent passing the session ID via URLs.
        ini_set('session.use_only_cookies', 1);
        // Prevent Javascript from reading cookie contents.
        ini_set('session.cookie_httponly', 1);
        // Ensure all cookies are sent via HTTPS.
        ini_set('session.cookie_secure', 1);
        // Prevent users from specifying their own session ID.
        ini_set('session.use_strict_mode', 1);
        // Moved from DBConnection. TODO: check if really needed.
        ini_set('memory_limit', '96M');
    }

    protected function registerAutoloaders(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    protected function processSettings(): void
    {
        $settingsFile = $this->getSettingsFile();
        if ($settingsFile === null)
        {
            throw new BootFailure('Geen instellingenbestand gevonden!');
        }

        $this->connectToDatabase($settingsFile);
    }

    protected function handleRequest(): void
    {
        new Router();
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