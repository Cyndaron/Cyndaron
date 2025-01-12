<?php
namespace Cyndaron\Minecraft;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use function count;
use function reset;

final class Member extends Model
{
    public const TABLE = 'minecraft_members';

    #[DatabaseField]
    public string $userName;
    #[DatabaseField]
    public string|null $uuid;
    #[DatabaseField]
    public string $realName;
    #[DatabaseField]
    public int $level;
    #[DatabaseField]
    public string $status;
    #[DatabaseField]
    public bool $donor;
    #[DatabaseField]
    public string $skinUrl;
    #[DatabaseField]
    public bool $renderAvatarHair;
    #[DatabaseField]
    public bool $newRenderer = false;

    public static function loadByUsername(string $username): self|null
    {
        $results = self::fetchAll(['userName = ?'], [$username]);
        if (count($results) === 0)
        {
            return null;
        }
        return reset($results);
    }
}
