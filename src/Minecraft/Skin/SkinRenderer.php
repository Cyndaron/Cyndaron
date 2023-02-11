<?php
namespace Cyndaron\Minecraft\Skin;

use GdImage;
use Symfony\Component\HttpFoundation\Response;

use function Safe\gmdate;
use function Safe\imagecolorat;
use function Safe\imagecopyresampled;
use function Safe\imagecreatefrompng;
use function Safe\imagecreatetruecolor;
use function Safe\imagesx;
use function Safe\imagesy;
use function time;
use function deg2rad;
use function cos;
use function sin;
use function explode;
use function microtime;
use function in_array;
use function array_diff;

abstract class SkinRenderer
{
    public const ALL_FACES = [
        'back',
        'right',
        'top',
        'front',
        'left',
        'bottom',
    ];

    public const VISIBLE_FACES_FORMAT = [
        'front' => [],
        'back' => [],
    ];

    public const CUBE_FACES_FORMAT = [
        'front' => [],
        'back' => [],
        'top' => [],
        'bottom' => [],
        'right' => [],
        'left' => [],
    ];

    public const LEG_LENGTH = 12;
    public const ARM_LENGTH = 12;
    public const TORSO_LENGTH = 12;
    public const TORSO_WIDTH = 8;

    protected Skin $skin;
    protected GdImage $skinSource;

    protected float $alpha;
    protected float $omega;
    protected int $width;
    protected int $height;
    public static int $minX = 0;
    public static int $maxX = 0;
    public static int $minY = 0;
    public static int $maxY = 0;
    protected int $hdRatio;

    protected SkinRendererParameters $parameters;

    protected array $times = [];
    protected array $headers = [];
    protected array $partsAngles = [];

    public function __construct(Skin $skin, SkinRendererParameters $parameters)
    {
        $this->times[] = ['Start', $this->microtimeFloat()];

        $this->skin = $skin;
        $this->parameters = $parameters;
        // Rotation variables in radians (3D Rendering)
        $this->alpha = deg2rad($this->parameters->verticalRotation); // Vertical rotation on the X axis.
        $this->omega = deg2rad($this->parameters->horizontalRotation); // Horizontal rotation on the Y axis.
    }

    abstract protected function setupTarget(): void;
    abstract protected function addPolygon(Polygon $poly): void;
    abstract protected function output(): Response;

    public function downloadImage(): void
    {
        $this->skinSource = $this->skin->getSkinOrFallback();

        $this->width = imagesx($this->skinSource);
        $this->height = imagesy($this->skinSource);
        if ($this->height === $this->width)
        {
            $skinSourceOld = $this->skinSource;
            $this->skinSource = imagecreatetruecolor($this->width, $this->height / 2);
            imagecopyresampled($this->skinSource, $skinSourceOld, 0, 0, 0, 0, $this->width, $this->height / 2, $this->width, $this->height / 2);
            $this->height = imagesy($this->skinSource);
        }
        elseif ($this->width !== ($this->height * 2) || ($this->height % 32) !== 0)
        {
            // Bad ratio created
            $this->skinSource = imagecreatefrompng(Skin::FALLBACK_IMAGE);
            $this->width = imagesx($this->skinSource);
            $this->height = imagesy($this->skinSource);
        }

        $this->hdRatio = $this->height / 32; // Set HD ratio to 2 if the skin is 128x64
        $this->times[] = ['Download-Image', $this->microtimeFloat()];
    }

    public function render(): Response
    {
        $this->downloadImage();
        $this->determinePartsAngles();

        $visibleFaces = $this->determineVisibleFaces($this->partsAngles);

        $cubePoints = $this->generateCubePoints();
        $cubeMaxDepthFaces = $cubePoints[0];

        foreach ($cubePoints as $cubePoint)
        {
            $cubePoint->getPoint()->project();
            if ($cubeMaxDepthFaces->getPoint()->getDepth() > $cubePoint->getPoint()->getDepth())
            {
                $cubeMaxDepthFaces = $cubePoint;
            }
        }
        $backFaces = $cubeMaxDepthFaces->getPlaces();
        $frontFaces = array_diff(self::ALL_FACES, $backFaces);

        $this->times[] = ['Determination-of-faces', $this->microtimeFloat()];

        $polygons = $this->determinePolygons();

        $this->times[] = ['Polygon-generation', $this->microtimeFloat()];

        $this->rotateMembers($polygons, $this->partsAngles, $this->parameters->displayHair);

        $this->times[] = ['Members-rotation', $this->microtimeFloat()];

        foreach ($polygons as $piece)
        {
            foreach ($piece as $face)
            {
                foreach ($face as $poly)
                {
                    if (!$poly->isProjected())
                    {
                        $poly->project();
                    }
                }
            }
        }

        $this->times[] = ['Projection-plan', $this->microtimeFloat()];

        /** @phpstan-ignore-next-line */
        if (Skin::SECONDS_TO_CACHE > 0)
        {
            $ts = gmdate('D, d M Y H:i:s', time() + Skin::SECONDS_TO_CACHE) . ' GMT';
            $this->headers['Expires'] = $ts;
            $this->headers['Pragma'] = 'cache';
            $this->headers['Cache-Control'] = 'max-age=' . Skin::SECONDS_TO_CACHE;
        }

        $this->setupTarget();

        $displayOrder = $this->determineDisplayOrder($frontFaces, $backFaces, $visibleFaces);

        $this->times[] = ['Calculated-display-faces', $this->microtimeFloat()];

        foreach ($displayOrder as $pieces)
        {
            foreach ($pieces as $piece => $faces)
            {
                foreach ($faces as $face)
                {
                    foreach ($polygons[$piece][$face] as $poly)
                    {
                        $this->addPolygon($poly);
                    }
                }
            }
        }

        $this->times[] = ['Display-image', $this->microtimeFloat()];

        return $this->output();
    }

    public function determinePartsAngles(): void
    {
        // Head, Helmet, Torso, Arms, Legs
        $parts_angles = [];
        $parts_angles['torso'] = [
            'cos_alpha' => cos(0),
            'sin_alpha' => sin(0),
            'cos_omega' => cos(0),
            'sin_omega' => sin(0),
        ];
        $alpha_head = 0;
        $omega_head = deg2rad((float)$this->parameters->hrh);
        $parts_angles['head'] = [
            'cos_alpha' => cos($alpha_head),
            'sin_alpha' => sin($alpha_head),
            'cos_omega' => cos($omega_head),
            'sin_omega' => sin($omega_head),
        ];
        $parts_angles['helmet'] = [
            'cos_alpha' => cos($alpha_head),
            'sin_alpha' => sin($alpha_head),
            'cos_omega' => cos($omega_head),
            'sin_omega' => sin($omega_head),
        ];
        $alpha_right_arm = deg2rad((float)$this->parameters->vrra);
        $omega_right_arm = 0;
        $parts_angles['rightArm'] = [
            'cos_alpha' => cos($alpha_right_arm),
            'sin_alpha' => sin($alpha_right_arm),
            'cos_omega' => cos($omega_right_arm),
            'sin_omega' => sin($omega_right_arm),
        ];
        $alpha_left_arm = deg2rad((float)$this->parameters->vrla);
        $omega_left_arm = 0;
        $parts_angles['leftArm'] = [
            'cos_alpha' => cos($alpha_left_arm),
            'sin_alpha' => sin($alpha_left_arm),
            'cos_omega' => cos($omega_left_arm),
            'sin_omega' => sin($omega_left_arm),
        ];
        $alpha_right_leg = deg2rad((float)$this->parameters->vrrl);
        $omega_right_leg = 0;
        $parts_angles['rightLeg'] = [
            'cos_alpha' => cos($alpha_right_leg),
            'sin_alpha' => sin($alpha_right_leg),
            'cos_omega' => cos($omega_right_leg),
            'sin_omega' => sin($omega_right_leg),
        ];
        $alpha_left_leg = deg2rad((float)$this->parameters->vrll);
        $omega_left_leg = 0;
        $parts_angles['leftLeg'] = [
            'cos_alpha' => cos($alpha_left_leg),
            'sin_alpha' => sin($alpha_left_leg),
            'cos_omega' => cos($omega_left_leg),
            'sin_omega' => sin($omega_left_leg),
        ];

        $this->partsAngles = $parts_angles;

        $this->times[] = ['Angle-Calculations', $this->microtimeFloat()];
    }

    /**
     * Returns timing in microseconds - used to calculate time taken to process images
     * @return float
     */
    protected function microtimeFloat(): float
    {
        $micro = explode(' ', microtime());
        return (float)$micro[0] + (float)$micro[1];
    }

    /**
     * @return CubePoint[]
     */
    private function generateCubePoints(): array
    {
        $cubePoints = [];
        $cubePoints[0] = new CubePoint(new Point(new CoordsXYZ(0, 0, 0), $this->alpha, $this->omega), ['back', 'right', 'top']);
        $cubePoints[1] = new CubePoint(new Point(new CoordsXYZ(0, 0, 1), $this->alpha, $this->omega), ['front', 'right', 'top']);
        $cubePoints[2] = new CubePoint(new Point(new CoordsXYZ(0, 1, 0), $this->alpha, $this->omega), ['back', 'right', 'bottom']);
        $cubePoints[3] = new CubePoint(new Point(new CoordsXYZ(0, 1, 1), $this->alpha, $this->omega), ['front', 'right', 'bottom']);
        $cubePoints[4] = new CubePoint(new Point(new CoordsXYZ(1, 0, 0), $this->alpha, $this->omega), ['back', 'left', 'top']);
        $cubePoints[5] = new CubePoint(new Point(new CoordsXYZ(1, 0, 1), $this->alpha, $this->omega), ['front', 'left', 'top']);
        $cubePoints[6] = new CubePoint(new Point(new CoordsXYZ(1, 1, 0), $this->alpha, $this->omega), ['back', 'left', 'bottom']);
        $cubePoints[7] = new CubePoint(new Point(new CoordsXYZ(1, 1, 1), $this->alpha, $this->omega), ['front', 'left', 'bottom']);
        return $cubePoints;
    }

    private function determineDisplayOrder(array $frontFaces, array $backFaces, array $visibleFaces): array
    {
        $display_order = [];
        if (in_array('top', $frontFaces, true))
        {
            if (in_array('right', $frontFaces, true))
            {
                $display_order[] = ['leftLeg' => $backFaces];
                $display_order[] = ['leftLeg' => $visibleFaces['leftLeg']['front']];
                $display_order[] = ['rightLeg' => $backFaces];
                $display_order[] = ['rightLeg' => $visibleFaces['rightLeg']['front']];
                $display_order[] = ['leftArm' => $backFaces];
                $display_order[] = ['leftArm' => $visibleFaces['leftArm']['front']];
                $display_order[] = ['torso' => $backFaces];
                $display_order[] = ['torso' => $visibleFaces['torso']['front']];
                $display_order[] = ['rightArm' => $backFaces];
                $display_order[] = ['rightArm' => $visibleFaces['rightArm']['front']];
            }
            else
            {
                $display_order[] = ['rightLeg' => $backFaces];
                $display_order[] = ['rightLeg' => $visibleFaces['rightLeg']['front']];
                $display_order[] = ['leftLeg' => $backFaces];
                $display_order[] = ['leftLeg' => $visibleFaces['leftLeg']['front']];
                $display_order[] = ['rightArm' => $backFaces];
                $display_order[] = ['rightArm' => $visibleFaces['rightArm']['front']];
                $display_order[] = ['torso' => $backFaces];
                $display_order[] = ['torso' => $visibleFaces['torso']['front']];
                $display_order[] = ['leftArm' => $backFaces];
                $display_order[] = ['leftArm' => $visibleFaces['leftArm']['front']];
            }
            $display_order[] = ['helmet' => $backFaces];
            $display_order[] = ['head' => $backFaces];
            $display_order[] = ['head' => $visibleFaces['head']['front']];
            $display_order[] = ['helmet' => $visibleFaces['head']['front']];
        }
        else
        {
            $display_order[] = ['helmet' => $backFaces];
            $display_order[] = ['head' => $backFaces];
            $display_order[] = ['head' => $visibleFaces['head']['front']];
            $display_order[] = ['helmet' => $visibleFaces['head']['front']];
            if (in_array('right', $frontFaces, true))
            {
                $display_order[] = ['leftArm' => $backFaces];
                $display_order[] = ['leftArm' => $visibleFaces['leftArm']['front']];
                $display_order[] = ['torso' => $backFaces];
                $display_order[] = ['torso' => $visibleFaces['torso']['front']];
                $display_order[] = ['rightArm' => $backFaces];
                $display_order[] = ['rightArm' => $visibleFaces['rightArm']['front']];
                $display_order[] = ['leftLeg' => $backFaces];
                $display_order[] = ['leftLeg' => $visibleFaces['leftLeg']['front']];
                $display_order[] = ['rightLeg' => $backFaces];
                $display_order[] = ['rightLeg' => $visibleFaces['rightLeg']['front']];
            }
            else
            {
                $display_order[] = ['rightArm' => $backFaces];
                $display_order[] = ['rightArm' => $visibleFaces['rightArm']['front']];
                $display_order[] = ['torso' => $backFaces];
                $display_order[] = ['torso' => $visibleFaces['torso']['front']];
                $display_order[] = ['leftArm' => $backFaces];
                $display_order[] = ['leftArm' => $visibleFaces['leftArm']['front']];
                $display_order[] = ['rightLeg' => $backFaces];
                $display_order[] = ['rightLeg' => $visibleFaces['rightLeg']['front']];
                $display_order[] = ['leftLeg' => $backFaces];
                $display_order[] = ['leftLeg' => $visibleFaces['leftLeg']['front']];
            }
        }

        return $display_order;
    }

    private function rotateMembers(array $polygons, array $parts_angles, bool $displayHair): void
    {
        foreach ($polygons['head'] as $face)
        {
            foreach ($face as $poly)
            {
                /** @var Polygon $poly */
                $poly->preProject(new CoordsXYZ(4, 8, 2), $parts_angles['head']['cos_alpha'], $parts_angles['head']['sin_alpha'], $parts_angles['head']['cos_omega'], $parts_angles['head']['sin_omega']);
            }
        }
        if ($displayHair)
        {
            foreach ($polygons['helmet'] as $face)
            {
                foreach ($face as $poly)
                {
                    /** @var Polygon $poly */
                    $poly->preProject(new CoordsXYZ(4, 8, 2), $parts_angles['head']['cos_alpha'], $parts_angles['head']['sin_alpha'], $parts_angles['head']['cos_omega'], $parts_angles['head']['sin_omega']);
                }
            }
        }

        foreach ($polygons['rightArm'] as $face)
        {
            foreach ($face as $poly)
            {
                /** @var Polygon $poly */
                $poly->preProject(new CoordsXYZ(-2, 8, 2), $parts_angles['rightArm']['cos_alpha'], $parts_angles['rightArm']['sin_alpha'], $parts_angles['rightArm']['cos_omega'], $parts_angles['rightArm']['sin_omega']);
            }
        }
        foreach ($polygons['leftArm'] as $face)
        {
            /** @var Polygon $poly */
            foreach ($face as $poly)
            {
                $poly->preProject(new CoordsXYZ(10, 8, 2), $parts_angles['leftArm']['cos_alpha'], $parts_angles['leftArm']['sin_alpha'], $parts_angles['leftArm']['cos_omega'], $parts_angles['leftArm']['sin_omega']);
            }
        }

        $z = ($parts_angles['rightLeg']['sin_alpha'] < 0 ? 0 : 4);
        foreach ($polygons['rightLeg'] as $face)
        {
            /** @var Polygon $poly */
            foreach ($face as $poly)
            {
                $poly->preProject(new CoordsXYZ(2, 20, $z), $parts_angles['rightLeg']['cos_alpha'], $parts_angles['rightLeg']['sin_alpha'], $parts_angles['rightLeg']['cos_omega'], $parts_angles['rightLeg']['sin_omega']);
            }
        }

        $z = ($parts_angles['leftLeg']['sin_alpha'] < 0 ? 0 : 4);
        foreach ($polygons['leftLeg'] as $face)
        {
            /** @var Polygon $poly */
            foreach ($face as $poly)
            {
                $poly->preProject(new CoordsXYZ(6, 20, $z), $parts_angles['leftLeg']['cos_alpha'], $parts_angles['leftLeg']['sin_alpha'], $parts_angles['leftLeg']['cos_omega'], $parts_angles['leftLeg']['sin_omega']);
            }
        }
    }

    private function determineHeadPolygons(): array
    {
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 9 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][-2 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][-2 * $this->hdRatio] = new Point(new CoordsXYZ($i, $j, -2 * $this->hdRatio), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][6 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][6 * $this->hdRatio] = new Point(new CoordsXYZ($i, $j, 6 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 9 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(0, $j, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[8 * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(8 * $this->hdRatio, $j, $faceName), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i, 0, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][8 * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][8 * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i, 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }

        $polygons = self::CUBE_FACES_FORMAT;
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 8 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (32 * $this->hdRatio - 1) - $i, 8 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][-2 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][-2 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][-2 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][-2 * $this->hdRatio],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + $i, 8 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][6 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][6 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][6 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][6 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 8 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, $faceName + 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (24 * $this->hdRatio - 1) - $faceName - 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[8 * $this->hdRatio][$j][$faceName],
                    $volumePoints[8 * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[8 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[8 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + $i, $faceName + 2 * $this->hdRatio));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 16 * $this->hdRatio + $i, (8 * $this->hdRatio - 1) - ($faceName + 2 * $this->hdRatio)));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][8 * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][8 * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][8 * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][8 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determineHairPolygons(): array
    {
        $polygons = self::CUBE_FACES_FORMAT;
        if (!$this->parameters->displayHair)
        {
            return $polygons;
        }

        // HELMET/HAIR
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 9 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][-2 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][-2 * $this->hdRatio] = new Point(new CoordsXYZ($i * 9 / 8 - 0.5 * $this->hdRatio, $j * 9 / 8 - 0.5 * $this->hdRatio, -2.5 * $this->hdRatio), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][6 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][6 * $this->hdRatio] = new Point(new CoordsXYZ($i * 9 / 8 - 0.5 * $this->hdRatio, $j * 9 / 8 - 0.5 * $this->hdRatio, 6.5 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 9 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(-0.5 * $this->hdRatio, $j * 9 / 8 - 0.5 * $this->hdRatio, $faceName * 9 / 8 - 0.5 * $this->hdRatio), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[8 * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(8.5 * $this->hdRatio, $j * 9 / 8 - 0.5 * $this->hdRatio, $faceName * 9 / 8 - 0.5 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i * 9 / 8 - 0.5 * $this->hdRatio, -0.5 * $this->hdRatio, $faceName * 9 / 8 - 0.5 * $this->hdRatio), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][8 * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][8 * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i * 9 / 8 - 0.5 * $this->hdRatio, 8.5 * $this->hdRatio, $faceName * 9 / 8 - 0.5 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 8 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + (32 * $this->hdRatio - 1) - $i, 8 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][-2 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][-2 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][-2 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][-2 * $this->hdRatio],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + 8 * $this->hdRatio + $i, 8 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][6 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][6 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][6 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][6 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 8 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + $faceName + 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + (24 * $this->hdRatio - 1) - $faceName - 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[8 * $this->hdRatio][$j][$faceName],
                    $volumePoints[8 * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[8 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[8 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + 8 * $this->hdRatio + $i, $faceName + 2 * $this->hdRatio));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + 16 * $this->hdRatio + $i, (8 * $this->hdRatio - 1) - ($faceName + 2 * $this->hdRatio)));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][8 * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][8 * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][8 * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][8 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determineTorsoPolygons(): array
    {
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][0]))
                {
                    $volumePoints[$i][$j][0] = new Point(new CoordsXYZ($i, $j + self::TORSO_WIDTH * $this->hdRatio, 0), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][4 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][4 * $this->hdRatio] = new Point(new CoordsXYZ($i, $j + self::TORSO_WIDTH * $this->hdRatio, 4 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(0, $j + self::TORSO_WIDTH * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[self::TORSO_WIDTH * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[self::TORSO_WIDTH * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(self::TORSO_WIDTH * $this->hdRatio, $j + self::TORSO_WIDTH * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i, 0 + self::TORSO_WIDTH * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][self::TORSO_LENGTH * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][self::TORSO_LENGTH * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i, self::TORSO_LENGTH * $this->hdRatio + self::TORSO_WIDTH * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }

        $polygons = self::CUBE_FACES_FORMAT;
        for ($i = 0; $i < self::TORSO_WIDTH * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < self::TORSO_LENGTH * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (40 * $this->hdRatio - 1) - $i, 20 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][0],
                    $volumePoints[$i + 1][$j][0],
                    $volumePoints[$i + 1][$j + 1][0],
                    $volumePoints[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 20 * $this->hdRatio + $i, 20 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < self::TORSO_LENGTH * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 16 * $this->hdRatio + $faceName, 20 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (32 * $this->hdRatio - 1) - $faceName, 20 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[self::TORSO_WIDTH * $this->hdRatio][$j][$faceName],
                    $volumePoints[self::TORSO_WIDTH * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[self::TORSO_WIDTH * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[self::TORSO_WIDTH * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < self::TORSO_WIDTH * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 20 * $this->hdRatio + $i, 16 * $this->hdRatio + $faceName));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 28 * $this->hdRatio + $i, (20 * $this->hdRatio - 1) - $faceName));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][self::TORSO_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::TORSO_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::TORSO_LENGTH * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][self::TORSO_LENGTH * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determineRightArmPolygons(): array
    {
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][0]))
                {
                    $volumePoints[$i][$j][0] = new Point(new CoordsXYZ($i - 4 * $this->hdRatio, $j + 8 * $this->hdRatio, 0), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][4 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][4 * $this->hdRatio] = new Point(new CoordsXYZ($i - 4 * $this->hdRatio, $j + 8 * $this->hdRatio, 4 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(0 - 4 * $this->hdRatio, $j + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[4 * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(4 * $this->hdRatio - 4 * $this->hdRatio, $j + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i - 4 * $this->hdRatio, 0 + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i - 4 * $this->hdRatio, self::ARM_LENGTH * $this->hdRatio + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }

        $polygons = self::CUBE_FACES_FORMAT;
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < self::ARM_LENGTH * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (56 * $this->hdRatio - 1) - $i, 20 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][0],
                    $volumePoints[$i + 1][$j][0],
                    $volumePoints[$i + 1][$j + 1][0],
                    $volumePoints[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + $i, 20 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < self::ARM_LENGTH * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 40 * $this->hdRatio + $faceName, 20 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (52 * $this->hdRatio - 1) - $faceName, 20 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[4 * $this->hdRatio][$j][$faceName],
                    $volumePoints[4 * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + $i, 16 * $this->hdRatio + $faceName));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 48 * $this->hdRatio + $i, (20 * $this->hdRatio - 1) - $faceName));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::ARM_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::ARM_LENGTH * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determineLeftArmPolygons(): array
    {
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][0]))
                {
                    $volumePoints[$i][$j][0] = new Point(new CoordsXYZ($i + 8 * $this->hdRatio, $j + 8 * $this->hdRatio, 0), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][4 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][4 * $this->hdRatio] = new Point(new CoordsXYZ($i + 8 * $this->hdRatio, $j + 8 * $this->hdRatio, 4 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(0 + 8 * $this->hdRatio, $j + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[4 * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(4 * $this->hdRatio + 8 * $this->hdRatio, $j + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i + 8 * $this->hdRatio, 0 + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i + 8 * $this->hdRatio, self::ARM_LENGTH * $this->hdRatio + 8 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }

        $polygons = self::CUBE_FACES_FORMAT;
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < self::ARM_LENGTH * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (56 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][0],
                    $volumePoints[$i + 1][$j][0],
                    $volumePoints[$i + 1][$j + 1][0],
                    $volumePoints[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < self::ARM_LENGTH * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 40 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (52 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[4 * $this->hdRatio][$j][$faceName],
                    $volumePoints[4 * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 16 * $this->hdRatio + $faceName));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 48 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), (20 * $this->hdRatio - 1) - $faceName));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::ARM_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::ARM_LENGTH * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][self::ARM_LENGTH * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determineRightLegPolygons(): array
    {
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][0]))
                {
                    $volumePoints[$i][$j][0] = new Point(new CoordsXYZ($i, $j + 20 * $this->hdRatio, 0), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][4 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][4 * $this->hdRatio] = new Point(new CoordsXYZ($i, $j + 20 * $this->hdRatio, 4 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(0, $j + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[4 * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(4 * $this->hdRatio, $j + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i, 0 + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i, self::LEG_LENGTH * $this->hdRatio + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }

        $polygons = self::CUBE_FACES_FORMAT;
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < self::LEG_LENGTH * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (16 * $this->hdRatio - 1) - $i, 20 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][0],
                    $volumePoints[$i + 1][$j][0],
                    $volumePoints[$i + 1][$j + 1][0],
                    $volumePoints[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + $i, 20 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < self::LEG_LENGTH * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 0 + $faceName, 20 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (self::LEG_LENGTH * $this->hdRatio - 1) - $faceName, 20 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[4 * $this->hdRatio][$j][$faceName],
                    $volumePoints[4 * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + $i, 16 * $this->hdRatio + $faceName));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + $i, (20 * $this->hdRatio - 1) - $faceName));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::LEG_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::LEG_LENGTH * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determineLeftLegPolygons(): array
    {
        $volumePoints = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volumePoints[$i][$j][0]))
                {
                    $volumePoints[$i][$j][0] = new Point(new CoordsXYZ($i + 4 * $this->hdRatio, $j + 20 * $this->hdRatio, 0), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][$j][4 * $this->hdRatio]))
                {
                    $volumePoints[$i][$j][4 * $this->hdRatio] = new Point(new CoordsXYZ($i + 4 * $this->hdRatio, $j + 20 * $this->hdRatio, 4 * $this->hdRatio), $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[0][$j][$faceName]))
                {
                    $volumePoints[0][$j][$faceName] = new Point(new CoordsXYZ(0 + 4 * $this->hdRatio, $j + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volumePoints[4 * $this->hdRatio][$j][$faceName] = new Point(new CoordsXYZ(4 * $this->hdRatio + 4 * $this->hdRatio, $j + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volumePoints[$i][0][$faceName]))
                {
                    $volumePoints[$i][0][$faceName] = new Point(new CoordsXYZ($i + 4 * $this->hdRatio, 0 + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
                if (!isset($volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName]))
                {
                    $volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName] = new Point(new CoordsXYZ($i + 4 * $this->hdRatio, self::LEG_LENGTH * $this->hdRatio + 20 * $this->hdRatio, $faceName), $this->alpha, $this->omega);
                }
            }
        }

        $polygons = self::CUBE_FACES_FORMAT;
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < self::LEG_LENGTH * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (16 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['back'][] = new Polygon([
                    $volumePoints[$i][$j][0],
                    $volumePoints[$i + 1][$j][0],
                    $volumePoints[$i + 1][$j + 1][0],
                    $volumePoints[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['front'][] = new Polygon([
                    $volumePoints[$i][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j][4 * $this->hdRatio],
                    $volumePoints[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volumePoints[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < self::LEG_LENGTH * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 0 + ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['right'][] = new Polygon([
                    $volumePoints[0][$j][$faceName],
                    $volumePoints[0][$j][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName + 1],
                    $volumePoints[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (self::LEG_LENGTH * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['left'][] = new Polygon([
                    $volumePoints[4 * $this->hdRatio][$j][$faceName],
                    $volumePoints[4 * $this->hdRatio][$j][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volumePoints[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 16 * $this->hdRatio + $faceName));
                $polygons['top'][] = new Polygon([
                    $volumePoints[$i][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName],
                    $volumePoints[$i + 1][0][$faceName + 1],
                    $volumePoints[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), (20 * $this->hdRatio - 1) - $faceName));
                $polygons['bottom'][] = new Polygon([
                    $volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::LEG_LENGTH * $this->hdRatio][$faceName],
                    $volumePoints[$i + 1][self::LEG_LENGTH * $this->hdRatio][$faceName + 1],
                    $volumePoints[$i][self::LEG_LENGTH * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

        return $polygons;
    }

    private function determinePolygons(): array
    {
        return [
            'helmet' => $this->determineHairPolygons(),
            'head' => $this->determineHeadPolygons(),
            'torso' => $this->determineTorsoPolygons(),
            'rightArm' => $this->determineRightArmPolygons(),
            'leftArm' => $this->determineLeftArmPolygons(),
            'rightLeg' => $this->determineRightLegPolygons(),
            'leftLeg' => $this->determineLeftLegPolygons(),
        ];
    }

    public function determineVisibleFaces(array $parts_angles): array
    {
        $visible_faces = [
            'head' => self::VISIBLE_FACES_FORMAT,
            'torso' => self::VISIBLE_FACES_FORMAT,
            'rightArm' => self::VISIBLE_FACES_FORMAT,
            'leftArm' => self::VISIBLE_FACES_FORMAT,
            'rightLeg' => self::VISIBLE_FACES_FORMAT,
            'leftLeg' => self::VISIBLE_FACES_FORMAT,
        ];

        // Loop each preProject and Project then calculate the visible faces for each - also display
        foreach ($visible_faces as $faceName => &$faceFormat)
        {
            $cubePoints = $this->generateCubePoints();
            $cubeMaxDepthFaces = $cubePoints[0];

            foreach ($cubePoints as $cubePoint)
            {
                $cubePoint->getPoint()->preProject(new CoordsXYZ(0, 0, 0), $parts_angles[$faceName]['cos_alpha'], $parts_angles[$faceName]['sin_alpha'], $parts_angles[$faceName]['cos_omega'], $parts_angles[$faceName]['sin_omega']);
                $cubePoint->getPoint()->project();

                if ($cubeMaxDepthFaces->getPoint()->getDepth() > $cubePoint->getPoint()->getDepth())
                {
                    $cubeMaxDepthFaces = $cubePoint;
                }
            }
            $faceFormat['back'] = $cubeMaxDepthFaces->getPlaces();
            $faceFormat['front'] = array_diff(self::ALL_FACES, $faceFormat['back']);
        }

        return $visible_faces;
    }
}
