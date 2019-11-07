<?php
declare(strict_types=1);

namespace Cyndaron\Module;

class Datatype
{
    public $singular;
    public $plural;
    public $editorPage;
    public $editorSavePage;
    public $pageManagerTab;

    public static function fromArray(array $array): self
    {
        $object = new self();
        foreach ($array as $property => $value)
        {
            $object->$property = $value;
        }
        return $object;
    }
}