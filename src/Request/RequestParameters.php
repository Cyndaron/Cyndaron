<?php
namespace Cyndaron\Request;

class RequestParameters
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
     * @return mixed|null
     * @throws \Exception
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
            $parameter = mb_convert_encoding($parameter , 'UTF-8', 'UTF-8');
            // Remove control codes, except for \r and \n.
            $parameter = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $parameter);
        }
        elseif (!is_scalar($parameter))
        {
            throw new \Exception('Unrecognized parameter type');
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
        return filter_var($this->getUnfilteredString($name, $default), FILTER_SANITIZE_EMAIL);
    }

    public function getPhone(string $name, string $default = ''): string
    {
        return preg_replace('/[^0-9+\- ]/','', $this->getUnfilteredString($name, $default));
    }

    public function getUrl(string $name, string $default = ''): string
    {
        $preFilter = str_replace(['<', '>'], '', $this->getUnfilteredString($name, $default));
        return filter_var($preFilter, FILTER_SANITIZE_URL);
    }

    public function getColor(string $name, string $default = '#000000'): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        return preg_replace('/[^#0-9A-Za-z]/', '', $preFilter);
    }

    public function getAlphaNum(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        return preg_replace('/[^0-9A-Za-z]/', '', $preFilter);
    }

    /**
     * Get HTML string, with <script> tags and JS attributes filtered out.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getHTML(string $name, string $default = ''): string
    {
        $value = $this->getUnfilteredString($name, $default);
        // Remove <script> tags
        $value = preg_replace("/<script.*?>.*?<\/script>/ims",'', $value);
        // Remove onLoad/onClick/... attributes
        $value = preg_replace('/\bon\w+=\S+(?=.*>)/', '', $value);

        return $value;
    }

    public function getDate(string $name, ?string $default = null): ?string
    {
        $preFilter = $this->getUnfilteredString($name);
        if ($preFilter === '')
            return $default;

        return preg_replace('/[^0-9:\-] /', '', $preFilter);
    }

    public function getPostcode(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        return preg_replace('/[^0-9A-Za-z ]/', '', $preFilter);
    }

    public function getInitials(string $name, string $default = ''): string
    {
        $preFilter = strtoupper($this->getUnfilteredString($name, $default));
        return preg_replace('/[^A-Z.]/', '', $preFilter);
    }

    public function getTussenvoegsel(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        return preg_replace('/[^A-Za-z\' ]/', '', $preFilter);
    }

    /**
     * Remove all characters not desired in filenames, including forward slashes.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getFilename(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        return preg_replace('/[^A-Za-z0-9 \-+.]/', '', $preFilter);
    }

    /**
     * Same as getFilename(), but allow forward slashes.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getFilenameWithDirectory(string $name, string $default = ''): string
    {
        $preFilter = $this->getUnfilteredString($name, $default);
        return preg_replace('/[^A-Za-z0-9 \-+.\/]/', '', $preFilter);
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
        return filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_NO_ENCODE_QUOTES);
    }
}