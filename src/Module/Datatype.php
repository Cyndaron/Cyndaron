<?php
declare(strict_types=1);

namespace Cyndaron\Module;

class Datatype
{
    public string $singular;
    public string $plural;
    public string $editorPage;
    public string $editorSavePage;
    public string $pageManagerTab;

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
