<?php
namespace Cyndaron\Minecraft\Skin;

use Cyndaron\View\Template\Template;
use Symfony\Component\HttpFoundation\Response;
use function count;

final class SkinRendererSVG extends SkinRenderer
{
    private Template $template;
    private array $templateVars = [];

    protected function setupTarget(): void
    {
        $width = self::$maxX - self::$minX;
        $height = self::$maxY - self::$minY;

        $this->template = new Template();
        $this->templateVars = [
            'minX' => self::$minX,
            'minY' => self::$minY,
            'width' => $width,
            'height' => $height,
            'contents' => '',
        ];
    }

    protected function addPolygon(Polygon $poly): void
    {
        $this->templateVars['contents'] .= $poly->getSvgPolygon(1);
    }

    protected function output(): Response
    {
        $this->templateVars['remarks'] = '';
        for ($i = 1, $iMax = count($this->times); $i < $iMax; $i++)
        {
            $this->templateVars['remarks'] .= '<!-- ' . ($this->times[$i][1] - $this->times[$i - 1][1]) * 1000 . 'ms : ' . $this->times[$i][0] . ' -->' . "\n";
        }
        $this->templateVars['remarks'] .= '<!-- TOTAL : ' . ($this->times[count($this->times) - 1][1] - $this->times[0][1]) * 1000 . 'ms -->' . "\n";

        $this->headers['Content-Type'] = 'image/svg+xml';
        return new Response($this->template->render('Minecraft/Skin/SkinSVG', $this->templateVars), Response::HTTP_OK, $this->headers);
    }
}
