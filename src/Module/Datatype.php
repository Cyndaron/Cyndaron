<?php
declare(strict_types=1);

namespace Cyndaron\Module;

final class Datatype
{
    public string $singular;
    public string $plural;
    /** @var class-string */
    public string $editorPage;
    /** @var class-string */
    public string $editorSavePage;
    public string $pageManagerTab;
    public string $pageManagerJS;

    /**
     * @param array<string, mixed> $array
     * @return self
     */
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
