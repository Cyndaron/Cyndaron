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

        /**
         * Cyndaron autoloader (PSR-4)
         */
        spl_autoload_register(function ($class)
        {
            // project-specific namespace prefix
            $prefix = 'Cyndaron\\';

            // base directory for the namespace prefix
            $base_dir = __DIR__ . '/';

            // does the class use the namespace prefix?
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0)
            {
                // no, move to the next registered autoloader
                return;
            }

            // get the relative class name
            $relative_class = substr($class, $len);

            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            // if the file exists, require it
            if (file_exists($file))
            {
                require $file;
            }
        });

        /**
         * Contrib autoloader (PSR-4)
         */
        spl_autoload_register(function ($class)
        {
            // base directory for the namespace prefix
            $base_dir = __DIR__ . '/../contrib/';

            // replace the namespace prefix with the base directory, replace namespace
            // separators with directory separators in the relative class name, append
            // with .php
            $file = $base_dir . str_replace('\\', '/', $class) . '.php';

            // if the file exists, require it
            if (file_exists($file))
            {
                require $file;
            }
        });
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

            \Cyndaron\DBConnection::connect($dbmethode, $dbplek, $dbnaam, $dbuser, $dbpass);
        }
        else
        {
            echo 'Geen instellingenbestand gevonden!';
            die();
        }
    }

    protected function handleRequest()
    {
        $router = new \Cyndaron\Router();
    }
}