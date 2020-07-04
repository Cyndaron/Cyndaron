<?php
namespace Cyndaron\Minecraft\Skin;

use Cyndaron\Request\RequestParameters;

final class SkinRendererParameters
{
    public int $ratio;
    public int $verticalRotation;
    public int $horizontalRotation;
    public bool $displayHair = false;
    public int $hrh;
    public int $vrla;
    public int $vrra;
    public int $vrll;
    public int $vrrl;

    public static function fromRequestParameters(RequestParameters $request): self
    {
        $obj = new self();
        $obj->ratio = max($request->getInt('ratio'), 2);
        $obj->verticalRotation = $request->getInt('vr');
        $obj->horizontalRotation = $request->getInt('hr');
        $obj->hrh = $request->getInt('hrh');
        $obj->vrla = $request->getInt('vrla');
        $obj->vrra = $request->getInt('vrra');
        $obj->vrll = $request->getInt('vrll');
        $obj->vrrl = $request->getInt('vrrl');

        return $obj;
    }
}
