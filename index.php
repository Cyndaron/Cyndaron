<?php
// Geef sessie-ID enkel door via cookies, niet via URL's
ini_set('session.use_only_cookies', 1);
// Voorkom dat cookies door Javascript worden ingelezen
ini_set('session.cookie_httponly', 1);
// Zorg ervoor dat alle cookies over HTTPS worden verzonden
ini_set('session.cookie_secure', 1);
// Voorkom dat gebruikers zelf een sessie-ID kunnen opgeven
ini_set('session.use_strict_mode', 1);
// Moved from DBConnection. TODO: check if really needed.
ini_set('memory_limit', '96M');


if (!file_exists(__DIR__ . '/instellingen.php'))
{
    echo 'Geen instellingenbestand gevonden!';
    die();
}

include __DIR__ . '/instellingen.php';

/**
 * Cyndaron autoloader (PSR-4)
 */
spl_autoload_register(function ($class)
{
    // project-specific namespace prefix
    $prefix = 'Cyndaron\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

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
 * Vendor autoloader (PSR-4)
 */
spl_autoload_register(function ($class)
{
    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/vendor/';

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

\Cyndaron\DBConnection::connect();
$router = new \Cyndaron\Router();
