<?php
namespace Cyndaron\Minecraft;

use Cyndaron\Model;

final class Member extends Model
{
    public const TABLE = 'minecraft_members';
    public const TABLE_FIELDS = ['userName', 'uuid', 'realName', 'level', 'status', 'donor', 'skinUrl', 'renderAvatarHair', 'newRenderer'];

    public string $userName;
    public ?string $uuid;
    public string $realName;
    public int $level;
    public string $status;
    public bool $donor;
    public string $skinUrl;
    public bool $renderAvatarHair;
    public bool $newRenderer = false;

    public static function loadByUsername(string $username): ?self
    {
        $results = self::fetchAll(['userName = ?'], [$username]);
        if (count($results) === 0)
        {
            return null;
        }
        return reset($results);
    }
}
