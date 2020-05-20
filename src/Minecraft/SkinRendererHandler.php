<?php
namespace Cyndaron\Minecraft;

use Cyndaron\Request\RequestParameters;
use Symfony\Component\HttpFoundation\Response;

/* ***** MINECRAFT 3D Skin Generator *****
 * The contents of this project were first developed by Pierre Gros on 17th April 2012.
 * It has once been modified by Carlos Ferreira (http://www.carlosferreira.me) on 31st May 2014.
 * Translations done by Carlos Ferreira.
 *
 **** GET Parameters ****
 ** These parameters have been renamed to match their English translations **
 * user - Minecraft's username for the skin to be rendered.
 * vr - Vertical Rotation.
 * hr - Horizontal Rotation.
 *
 * hrh - Horizontal Rotation of the Head.
 *
 * vrll - Vertical Rotation of the Left Leg.
 * vrrl - Vertical Rotation of the Right Leg.
 * vrla - Vertical Rotation of the Left Arm.
 * vrra - Vertical Rotation of the Right Arm.
 *
 * displayHair - Either or not to display hairs. Set to "false" to NOT display hairs.
 *
 * format - The format in which the image is to be rendered. PNG ("png") is used by default set to "svg" to use a vector version.
 * ratio - The size of the "png" image. The default and minimum value is 2.
 */

class SkinRendererHandler
{
    private Member $user;
    private string $format;
    private SkinRendererParameters $parameters;

    public function __construct(Member $user, string $format, SkinRendererParameters $parameters)
    {
        $this->user = $user;
        $this->format = $format;
        $this->parameters = $parameters;
        $this->parameters->displayHair = $this->user->renderAvatarHair;
    }

    public function draw(): Response
    {
        $skin = new Skin($this->user->skinUrl);
        if ($this->format === 'svg')
        {
            $renderClass = SkinRendererSVG::class;
        }
        else
        {
            $renderClass = SkinRendererPNG::class;
        }

        /** @var SkinRenderer $renderer */
        $renderer = new $renderClass($skin, $this->parameters);
        return $renderer->render();
    }
}