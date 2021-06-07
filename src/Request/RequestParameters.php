<?php
namespace Cyndaron\Request;

use Exception;
use Safe\Exceptions\PcreException;
use function Safe\preg_replace;
use function array_keys;
use function is_array;
use function is_string;
use function mb_convert_encoding;
use function is_scalar;
use function array_key_exists;
use function str_replace;
use function strtoupper;
use function filter_var;

final class RequestParameters
{
    private array $vars;

    public function __construct(array $vars)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->vars = $this->stripInvalidCharacters($vars);
    }

    public function getRaw(): array
    {
        return $this->vars;
    }

    public function getKeys(): array
    {
        return array_keys($this->vars);
    }

    /**
     * @param mixed $parameter
     * @throws Exception
     * @return mixed|null
     */
    public function stripInvalidCharacters($parameter)
    {
        if (is_array($parameter))
        {
            foreach ($parameter as $key => $value)
            {
                $parameter[$key] = $this->stripInvalidCharacters($value);
            }
        }
        elseif (is_string($parameter))
        {
            // This will strip out invalid UTF-8.
            $parameter = mb_convert_encoding($parameter, 'UTF-8', 'UTF-8');
            // Remove control codes, except for \r and \n.
            $parameter = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $parameter);
        }
        elseif (!is_scalar($parameter))
        {
            throw new Exception('Unrecognized parameter type');
        }

        return $parameter;
    }

    public function isEmpty(): bool
    {
        return empty($this->vars);
    }

    public function hasVar(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    public function getInt(string $name, int $default = 0): int
    {
        if (!$this->hasVar($name))
        {
            return $default;
        }

        return (int)$this->vars[$name];
    }

    public function getFloat(string $name, float $default = 0.0): float
    {
        if (!$this->hasVar($name))
        {
            return $default;
        }

        $value = $this->vars[$name];
        $value = str_replace(',', '.', $value);

        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public function getBool(string $name, bool $default = false): bool
    {
        if (!$this->hasVar($name))
        {
            return $default;
        }

        return (bool)(int)$this->vars[$name];
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        if (!$this->hasVar($name))
        {
            return $default;
        }

        return $this->vars[$name];
    }

    public function getUnfilteredString(string $name, string $default = ''): string
    {
        if (!$this->hasVar($name))
        {
            return $default;
        }

        return (string)$this->vars[$name];
    }

    public function getEmail(string $name, string $default = ''): string
    {
        $ret = filter_var($this->getUnfilteredString($name, $default), FILTER_SANITIZE_EMAIL);
        return ($ret === false) ? $default : $ret;
    }

    public function getPhone(string $name, string $default = ''): string
    {
        /** @var string $result */
        $result = preg_replace('/[^0-9+\- ]/', '', $this->getUnfilteredString($name, $default));
        return $result;
    }

    public function getUrl(string $name, string $default = ''): string
    {
        $preFilter = str_replace(['<', '>'], '', $this->getUnfilteredString($name, $default));
        $ret = filter_var($preFilter, FILTER_SANITIZE_URL);
        return ($ret === false) ? $default : $ret;
    }

    public function getColor(string $name, string $default = '#000000'): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        /** @var string $result */
        $result = preg_replace('/[^#0-9A-Za-z]/', '', $preFilter);
        return $result;
    }

    public function getAlphaNum(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        /** @var string $result */
        $result = preg_replace('/[^0-9A-Za-z]/', '', $preFilter);
        return $result;
    }

    /**
     * Get HTML string, with <script> tags and JS attributes filtered out.
     *
     * @param string $name
     * @param string $default
     * @throws PcreException
     * @return string
     */
    public function getHTML(string $name, string $default = ''): string
    {
        $value = $this->getUnfilteredString($name, $default);
        // Remove <script> tags
        /** @var string $value */
        $value = preg_replace("/<script.*?>.*?<\/script>/ims", '', $value);
        // Remove onLoad/onClick/... attributes
        /** @var string $value */
        $value = preg_replace('/\bon\w+=\S+(?=.*>)/i', '', $value);

        return $value;
    }

    public function getDate(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name);
        if ($preFilter === '')
        {
            return $default;
        }

        /** @var string $result */
        $result = preg_replace('/[^0-9:\- ]/', '', $preFilter);
        return $result;
    }

    public function getPostcode(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        /** @var string $result */
        $result = preg_replace('/[^0-9A-Za-z ]/', '', $preFilter);
        return $result;
    }

    public function getInitials(string $name, string $default = ''): string
    {
        $preFilter = strtoupper($this->getUnfilteredString($name, $default));
        /** @var string $result */
        $result = preg_replace('/[^A-Z.]/', '', $preFilter);
        return $result;
    }

    public function getTussenvoegsel(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        /** @var string $result */
        $result = preg_replace('/[^A-Za-z\' ]/', '', $preFilter);
        return $result;
    }

    /**
     * Remove all characters not desired in filenames, including forward slashes.
     *
     * @param string $name
     * @param string $default
     * @throws PcreException
     * @return string
     */
    public function getFilename(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        /** @var string $result */
        $result = preg_replace('/[^A-Za-z0-9() \-+.]/', '', $preFilter);
        return $result;
    }

    /**
     * Same as getFilename(), but allow forward slashes.
     *
     * @param string $name
     * @param string $default
     * @throws PcreException
     * @return string
     */
    public function getFilenameWithDirectory(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        /** @var string $result */
        $result = preg_replace('/[^A-Za-z0-9 \-+.\/]/', '', $preFilter);
        return $result;
    }

    /**
     * A plain string without HTML tags, newlines or < > characters.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getSimpleString(string $name, string $default = ''): string
    {
        $value = $this->getUnfilteredString($name, $default);
        $ret = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_NO_ENCODE_QUOTES);
        return ($ret === false) ? $default : $ret;
    }
}
