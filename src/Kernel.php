<?php
namespace Cyndaron;

class Kernel
{
    public function __construct()
    {
    }

    public function boot()
    {
        $this->setPhpConfig();
        $this->registerAutoloaders();
        $this->processSettings();
        $this->handleRequest();
    }

    protected function setPhpConfig()
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

    protected function registerAutoloaders()
    {
        require_once __DIR__ . '/../vendor/autoload.php';
    }

    protected function processSettings()
    {
        static $settingsFiles = [
            __DIR__ . '/../config.php',
            __DIR__ . '/../instellingen.php',
        ];

        $foundSettingsFile = false;
        foreach ($settingsFiles as $settingsFile)
        {
            if (file_exists($settingsFile))
            {
                $foundSettingsFile = true;
                break;
            }
        }

        if ($foundSettingsFile)
        {
            $dbmethode = 'mysql';
            $dbuser = 'root';
            $dbpass = '';
            $dbplek = 'localhost';
            $dbnaam = 'cyndaron';

            /** @noinspection PhpUndefinedVariableInspection,PhpIncludeInspection */
            include $settingsFile;

            DBConnection::connect($dbmethode, $dbplek, $dbnaam, $dbuser, $dbpass);
        }
        else
        {
            echo 'Geen instellingenbestand gevonden!';
            die();
        }
    }

    protected function handleRequest()
    {
        new Router();
    }
}