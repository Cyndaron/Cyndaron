<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

namespace Cyndaron\Page;

use Cyndaron\DBAL\Model;
use function array_key_exists;
use function array_merge;
use function assert;
use function is_array;

class Page implements Pageable
{
    public string $title = '';

    /** @var string[] */
    public array $extraScripts = [];
    /** @var string[] */
    public array $extraCss = [];
    public string $extraBodyClasses = '';

    public Model|null $model = null;

    public string $template = '';
    /** @var array<string, mixed> */
    public array $templateVars = ['contents' => ''];

    public function addScript(string $filename): void
    {
        $this->extraScripts[] = $filename;
    }

    public function addCss(string $filename): void
    {
        $this->extraCss[] = $filename;
    }

    /**
     * @param string $varName
     * @param mixed $var
     */
    public function addTemplateVar(string $varName, mixed $var): void
    {
        $this->templateVars[$varName] = $var;
    }

    /**
     * @param array<string, mixed> $vars
     * @return void
     */
    public function addTemplateVars(array $vars): void
    {
        $this->templateVars = array_merge($this->templateVars, $vars);
    }

    public function addHeadLine(string $line): void
    {
        if (!array_key_exists('extraHeadLines', $this->templateVars))
        {
            $this->templateVars['extraHeadLines'] = [];
        }

        assert(is_array($this->templateVars['extraHeadLines']));
        $this->templateVars['extraHeadLines'][] = $line;
    }

    public function getTemplateVar(string $name): mixed
    {
        return $this->templateVars[$name] ?? null;
    }

    public function toPage(): Page
    {
        return $this;
    }
}
