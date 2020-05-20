<?php
namespace Cyndaron\Minecraft;

use Symfony\Component\HttpFoundation\Response;

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

    protected Skin $skin;
    protected $skinSource;
    
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
        $this->times[] = ['Start', $this->microtime_float()];

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
        elseif (!($this->width === $this->height * 2) || $this->height % 32 !== 0)
        {
            // Bad ratio created
            $this->skinSource = imagecreatefrompng(Skin::FALLBACK_IMAGE);
            $this->width = imagesx($this->skinSource);
            $this->height = imagesy($this->skinSource);
        }

        $this->hdRatio = $this->height / 32; // Set HD ratio to 2 if the skin is 128x64
        $this->times[] = ['Download-Image', $this->microtime_float()];
    }

    public function render(): Response
    {
        $this->downloadImage();
        $this->determinePartsAngles();

        $visible_faces = $this->determineVisibleFaces($this->partsAngles);

        $cubePoints = $this->generateCubePoints();
        $cube_max_depth_faces = $cubePoints[0];

        foreach ($cubePoints as $cubePoint)
        {
            $cubePoint->getPoint()->project();
            if ($cube_max_depth_faces->getPoint()->getDepth() > $cubePoint->getPoint()->getDepth())
            {
                $cube_max_depth_faces = $cubePoint;
            }

        }
        $back_faces = $cube_max_depth_faces->getPlaces();
        $front_faces = array_diff(self::ALL_FACES, $back_faces);

        $this->times[] = ['Determination-of-faces', $this->microtime_float()];

        $polygons = $this->determinePolygons();

        $this->times[] = ['Polygon-generation', $this->microtime_float()];

        $this->rotateMembers($polygons, $this->partsAngles, $this->parameters->displayHair);

        $this->times[] = ['Members-rotation', $this->microtime_float()];

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

        $this->times[] = ['Projection-plan', $this->microtime_float()];

        if (Skin::SECONDS_TO_CACHE > 0)
        {
            $ts = gmdate('D, d M Y H:i:s', time() + Skin::SECONDS_TO_CACHE) . ' GMT';
            $this->headers['Expires'] = $ts;
            $this->headers['Pragma'] = 'cache';
            $this->headers['Cache-Control'] = 'max-age=' . Skin::SECONDS_TO_CACHE;
        }

        $this->setupTarget();

        $displayOrder = $this->determineDisplayOrder($front_faces, $back_faces, $visible_faces);

        $this->times[] = ['Calculated-display-faces', $this->microtime_float()];

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

        $this->times[] = ['Display-image', $this->microtime_float()];

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

        $this->times[] = ['Angle-Calculations', $this->microtime_float()];
    }

    /**
     * Returns timing in microseconds - used to calculate time taken to process images
     * @return float
     */
    protected function microtime_float(): float
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
        $cubePoints[0] = new CubePoint(new Point(['x' => 0, 'y' => 0, 'z' => 0], $this->alpha, $this->omega), ['back', 'right', 'top']);
        $cubePoints[1] = new CubePoint(new Point(['x' => 0, 'y' => 0, 'z' => 1], $this->alpha, $this->omega), ['front', 'right', 'top']);
        $cubePoints[2] = new CubePoint(new Point(['x' => 0, 'y' => 1, 'z' => 0], $this->alpha, $this->omega), ['back', 'right', 'bottom']);
        $cubePoints[3] = new CubePoint(new Point(['x' => 0, 'y' => 1, 'z' => 1], $this->alpha, $this->omega), ['front', 'right', 'bottom']);
        $cubePoints[4] = new CubePoint(new Point(['x' => 1, 'y' => 0, 'z' => 0], $this->alpha, $this->omega), ['back', 'left', 'top']);
        $cubePoints[5] = new CubePoint(new Point(['x' => 1, 'y' => 0, 'z' => 1], $this->alpha, $this->omega), ['front', 'left', 'top']);
        $cubePoints[6] = new CubePoint(new Point(['x' => 1, 'y' => 1, 'z' => 0], $this->alpha, $this->omega), ['back', 'left', 'bottom']);
        $cubePoints[7] = new CubePoint(new Point(['x' => 1, 'y' => 1, 'z' => 1], $this->alpha, $this->omega), ['front', 'left', 'bottom']);
        return $cubePoints;
    }

    private function determineDisplayOrder($frontFaces, $backFaces, $visibleFaces): array
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
                $poly->preProject(4, 8, 2, $parts_angles['head']['cos_alpha'], $parts_angles['head']['sin_alpha'], $parts_angles['head']['cos_omega'], $parts_angles['head']['sin_omega']);
            }
        }
        if ($displayHair)
        {
            foreach ($polygons['helmet'] as $face)
            {
                foreach ($face as $poly)
                {
                    /** @var Polygon $poly */
                    $poly->preProject(4, 8, 2, $parts_angles['head']['cos_alpha'], $parts_angles['head']['sin_alpha'], $parts_angles['head']['cos_omega'], $parts_angles['head']['sin_omega']);
                }
            }
        }

        foreach ($polygons['rightArm'] as $face)
        {
            foreach ($face as $poly)
            {
                /** @var Polygon $poly */
                $poly->preProject(-2, 8, 2, $parts_angles['rightArm']['cos_alpha'], $parts_angles['rightArm']['sin_alpha'], $parts_angles['rightArm']['cos_omega'], $parts_angles['rightArm']['sin_omega']);
            }
        }
        foreach ($polygons['leftArm'] as $face)
        {
            /** @var Polygon $poly */
            foreach ($face as $poly)
            {
                $poly->preProject(10, 8, 2, $parts_angles['leftArm']['cos_alpha'], $parts_angles['leftArm']['sin_alpha'], $parts_angles['leftArm']['cos_omega'], $parts_angles['leftArm']['sin_omega']);
            }
        }
        foreach ($polygons['rightLeg'] as $face)
        {
            /** @var Polygon $poly */
            foreach ($face as $poly)
            {
                $poly->preProject(2, 20, ($parts_angles['rightLeg']['sin_alpha'] < 0 ? 0 : 4), $parts_angles['rightLeg']['cos_alpha'], $parts_angles['rightLeg']['sin_alpha'], $parts_angles['rightLeg']['cos_omega'], $parts_angles['rightLeg']['sin_omega']);
            }
        }
        foreach ($polygons['leftLeg'] as $face)
        {
            /** @var Polygon $poly */
            foreach ($face as $poly)
            {
                $poly->preProject(6, 20, ($parts_angles['leftLeg']['sin_alpha'] < 0 ? 0 : 4), $parts_angles['leftLeg']['cos_alpha'], $parts_angles['leftLeg']['sin_alpha'], $parts_angles['leftLeg']['cos_omega'], $parts_angles['leftLeg']['sin_omega']);
            }
        }
    }

    private function determineHeadPolygons(array &$polygons): void
    {
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 9 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][-2 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][-2 * $this->hdRatio] = new Point(['x' => $i, 'y' => $j, 'z' => -2 * $this->hdRatio], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][6 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][6 * $this->hdRatio] = new Point(['x' => $i, 'y' => $j, 'z' => 6 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 9 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => 0, 'y' => $j, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[8 * $this->hdRatio][$j][$faceName] = new Point(['x' => 8 * $this->hdRatio, 'y' => $j, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i, 'y' => 0, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][8 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][8 * $this->hdRatio][$faceName] = new Point(['x' => $i, 'y' => 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 8 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (32 * $this->hdRatio - 1) - $i, 8 * $this->hdRatio + $j));
                $polygons['head']['back'][] = new Polygon([
                    $volume_points[$i][$j][-2 * $this->hdRatio],
                    $volume_points[$i + 1][$j][-2 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][-2 * $this->hdRatio],
                    $volume_points[$i][$j + 1][-2 * $this->hdRatio],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + $i, 8 * $this->hdRatio + $j));
                $polygons['head']['front'][] = new Polygon([
                    $volume_points[$i][$j][6 * $this->hdRatio],
                    $volume_points[$i + 1][$j][6 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][6 * $this->hdRatio],
                    $volume_points[$i][$j + 1][6 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 8 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, $faceName + 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['head']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (24 * $this->hdRatio - 1) - $faceName - 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['head']['left'][] = new Polygon([
                    $volume_points[8 * $this->hdRatio][$j][$faceName],
                    $volume_points[8 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[8 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[8 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + $i, $faceName + 2 * $this->hdRatio));
                $polygons['head']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 16 * $this->hdRatio + $i, (8 * $this->hdRatio - 1) - ($faceName + 2 * $this->hdRatio)));
                $polygons['head']['bottom'][] = new Polygon([
                    $volume_points[$i][8 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][8 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][8 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][8 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }
    }

    private function determineHairPolygons(array &$polygons): void
    {
        if (!$this->parameters->displayHair)
        {
            return;
        }

        // HELMET/HAIR
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 9 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][-2 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][-2 * $this->hdRatio] = new Point(['x' => $i * 9 / 8 - 0.5 * $this->hdRatio, 'y' => $j * 9 / 8 - 0.5 * $this->hdRatio, 'z' => -2.5 * $this->hdRatio], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][6 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][6 * $this->hdRatio] = new Point(['x' => $i * 9 / 8 - 0.5 * $this->hdRatio, 'y' => $j * 9 / 8 - 0.5 * $this->hdRatio, 'z' => 6.5 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 9 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => -0.5 * $this->hdRatio, 'y' => $j * 9 / 8 - 0.5 * $this->hdRatio, 'z' => $faceName * 9 / 8 - 0.5 * $this->hdRatio], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[8 * $this->hdRatio][$j][$faceName] = new Point(['x' => 8.5 * $this->hdRatio, 'y' => $j * 9 / 8 - 0.5 * $this->hdRatio, 'z' => $faceName * 9 / 8 - 0.5 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 7 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i * 9 / 8 - 0.5 * $this->hdRatio, 'y' => -0.5 * $this->hdRatio, 'z' => $faceName * 9 / 8 - 0.5 * $this->hdRatio], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][8 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][8 * $this->hdRatio][$faceName] = new Point(['x' => $i * 9 / 8 - 0.5 * $this->hdRatio, 'y' => 8.5 * $this->hdRatio, 'z' => $faceName * 9 / 8 - 0.5 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 8 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + (32 * $this->hdRatio - 1) - $i, 8 * $this->hdRatio + $j));
                $polygons['helmet']['back'][] = new Polygon([
                    $volume_points[$i][$j][-2 * $this->hdRatio],
                    $volume_points[$i + 1][$j][-2 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][-2 * $this->hdRatio],
                    $volume_points[$i][$j + 1][-2 * $this->hdRatio],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + 8 * $this->hdRatio + $i, 8 * $this->hdRatio + $j));
                $polygons['helmet']['front'][] = new Polygon([
                    $volume_points[$i][$j][6 * $this->hdRatio],
                    $volume_points[$i + 1][$j][6 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][6 * $this->hdRatio],
                    $volume_points[$i][$j + 1][6 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 8 * $this->hdRatio; $j++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + $faceName + 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['helmet']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + (24 * $this->hdRatio - 1) - $faceName - 2 * $this->hdRatio, 8 * $this->hdRatio + $j));
                $polygons['helmet']['left'][] = new Polygon([
                    $volume_points[8 * $this->hdRatio][$j][$faceName],
                    $volume_points[8 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[8 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[8 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($faceName = -2 * $this->hdRatio; $faceName < 6 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + 8 * $this->hdRatio + $i, $faceName + 2 * $this->hdRatio));
                $polygons['helmet']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 32 * $this->hdRatio + 16 * $this->hdRatio + $i, (8 * $this->hdRatio - 1) - ($faceName + 2 * $this->hdRatio)));
                $polygons['helmet']['bottom'][] = new Polygon([
                    $volume_points[$i][8 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][8 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][8 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][8 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }

    }

    private function determineTorsoPolygons(array &$polygons): void
    {
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][0]))
                {
                    $volume_points[$i][$j][0] = new Point(['x' => $i, 'y' => $j + 8 * $this->hdRatio, 'z' => 0], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][4 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][4 * $this->hdRatio] = new Point(['x' => $i, 'y' => $j + 8 * $this->hdRatio, 'z' => 4 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => 0, 'y' => $j + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[8 * $this->hdRatio][$j][$faceName] = new Point(['x' => 8 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i, 'y' => 0 + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][12 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][12 * $this->hdRatio][$faceName] = new Point(['x' => $i, 'y' => 12 * $this->hdRatio + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 12 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (40 * $this->hdRatio - 1) - $i, 20 * $this->hdRatio + $j));
                $polygons['torso']['back'][] = new Polygon([
                    $volume_points[$i][$j][0],
                    $volume_points[$i + 1][$j][0],
                    $volume_points[$i + 1][$j + 1][0],
                    $volume_points[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 20 * $this->hdRatio + $i, 20 * $this->hdRatio + $j));
                $polygons['torso']['front'][] = new Polygon([
                    $volume_points[$i][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volume_points[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 12 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 16 * $this->hdRatio + $faceName, 20 * $this->hdRatio + $j));
                $polygons['torso']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (32 * $this->hdRatio - 1) - $faceName, 20 * $this->hdRatio + $j));
                $polygons['torso']['left'][] = new Polygon([
                    $volume_points[8 * $this->hdRatio][$j][$faceName],
                    $volume_points[8 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[8 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[8 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 8 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 20 * $this->hdRatio + $i, 16 * $this->hdRatio + $faceName));
                $polygons['torso']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 28 * $this->hdRatio + $i, (20 * $this->hdRatio - 1) - $faceName));
                $polygons['torso']['bottom'][] = new Polygon([
                    $volume_points[$i][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][12 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }
    }

    private function determineRightArmPolygons(array &$polygons): void
    {
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][0]))
                {
                    $volume_points[$i][$j][0] = new Point(['x' => $i - 4 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => 0], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][4 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][4 * $this->hdRatio] = new Point(['x' => $i - 4 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => 4 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => 0 - 4 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[4 * $this->hdRatio][$j][$faceName] = new Point(['x' => 4 * $this->hdRatio - 4 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i - 4 * $this->hdRatio, 'y' => 0 + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][12 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][12 * $this->hdRatio][$faceName] = new Point(['x' => $i - 4 * $this->hdRatio, 'y' => 12 * $this->hdRatio + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 12 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (56 * $this->hdRatio - 1) - $i, 20 * $this->hdRatio + $j));
                $polygons['rightArm']['back'][] = new Polygon([
                    $volume_points[$i][$j][0],
                    $volume_points[$i + 1][$j][0],
                    $volume_points[$i + 1][$j + 1][0],
                    $volume_points[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + $i, 20 * $this->hdRatio + $j));
                $polygons['rightArm']['front'][] = new Polygon([
                    $volume_points[$i][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volume_points[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 12 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 40 * $this->hdRatio + $faceName, 20 * $this->hdRatio + $j));
                $polygons['rightArm']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (52 * $this->hdRatio - 1) - $faceName, 20 * $this->hdRatio + $j));
                $polygons['rightArm']['left'][] = new Polygon([
                    $volume_points[4 * $this->hdRatio][$j][$faceName],
                    $volume_points[4 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + $i, 16 * $this->hdRatio + $faceName));
                $polygons['rightArm']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 48 * $this->hdRatio + $i, (20 * $this->hdRatio - 1) - $faceName));
                $polygons['rightArm']['bottom'][] = new Polygon([
                    $volume_points[$i][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][12 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }
    }

    private function determineLeftArmPolygons(array &$polygons): void
    {
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][0]))
                {
                    $volume_points[$i][$j][0] = new Point(['x' => $i + 8 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => 0], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][4 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][4 * $this->hdRatio] = new Point(['x' => $i + 8 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => 4 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => 0 + 8 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[4 * $this->hdRatio][$j][$faceName] = new Point(['x' => 4 * $this->hdRatio + 8 * $this->hdRatio, 'y' => $j + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i + 8 * $this->hdRatio, 'y' => 0 + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][12 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][12 * $this->hdRatio][$faceName] = new Point(['x' => $i + 8 * $this->hdRatio, 'y' => 12 * $this->hdRatio + 8 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 12 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (56 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['leftArm']['back'][] = new Polygon([
                    $volume_points[$i][$j][0],
                    $volume_points[$i + 1][$j][0],
                    $volume_points[$i + 1][$j + 1][0],
                    $volume_points[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['leftArm']['front'][] = new Polygon([
                    $volume_points[$i][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volume_points[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 12 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 40 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['leftArm']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (52 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['leftArm']['left'][] = new Polygon([
                    $volume_points[4 * $this->hdRatio][$j][$faceName],
                    $volume_points[4 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 44 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 16 * $this->hdRatio + $faceName));
                $polygons['leftArm']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 48 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), (20 * $this->hdRatio - 1) - $faceName));
                $polygons['leftArm']['bottom'][] = new Polygon([
                    $volume_points[$i][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][12 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }
    }

    private function determineRightLegPolygons(array &$polygons): void
    {
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][0]))
                {
                    $volume_points[$i][$j][0] = new Point(['x' => $i, 'y' => $j + 20 * $this->hdRatio, 'z' => 0], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][4 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][4 * $this->hdRatio] = new Point(['x' => $i, 'y' => $j + 20 * $this->hdRatio, 'z' => 4 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => 0, 'y' => $j + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[4 * $this->hdRatio][$j][$faceName] = new Point(['x' => 4 * $this->hdRatio, 'y' => $j + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i, 'y' => 0 + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][12 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][12 * $this->hdRatio][$faceName] = new Point(['x' => $i, 'y' => 12 * $this->hdRatio + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 12 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (16 * $this->hdRatio - 1) - $i, 20 * $this->hdRatio + $j));
                $polygons['rightLeg']['back'][] = new Polygon([
                    $volume_points[$i][$j][0],
                    $volume_points[$i + 1][$j][0],
                    $volume_points[$i + 1][$j + 1][0],
                    $volume_points[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + $i, 20 * $this->hdRatio + $j));
                $polygons['rightLeg']['front'][] = new Polygon([
                    $volume_points[$i][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volume_points[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 12 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 0 + $faceName, 20 * $this->hdRatio + $j));
                $polygons['rightLeg']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (12 * $this->hdRatio - 1) - $faceName, 20 * $this->hdRatio + $j));
                $polygons['rightLeg']['left'][] = new Polygon([
                    $volume_points[4 * $this->hdRatio][$j][$faceName],
                    $volume_points[4 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + $i, 16 * $this->hdRatio + $faceName));
                $polygons['rightLeg']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + $i, (20 * $this->hdRatio - 1) - $faceName));
                $polygons['rightLeg']['bottom'][] = new Polygon([
                    $volume_points[$i][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][12 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }
    }

    private function determineLeftLegPolygons(array &$polygons): void
    {
        $volume_points = [];
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 13 * $this->hdRatio; $j++)
            {
                if (!isset($volume_points[$i][$j][0]))
                {
                    $volume_points[$i][$j][0] = new Point(['x' => $i + 4 * $this->hdRatio, 'y' => $j + 20 * $this->hdRatio, 'z' => 0], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][$j][4 * $this->hdRatio]))
                {
                    $volume_points[$i][$j][4 * $this->hdRatio] = new Point(['x' => $i + 4 * $this->hdRatio, 'y' => $j + 20 * $this->hdRatio, 'z' => 4 * $this->hdRatio], $this->alpha, $this->omega);
                }
            }
        }
        for ($j = 0; $j < 13 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[0][$j][$faceName]))
                {
                    $volume_points[0][$j][$faceName] = new Point(['x' => 0 + 4 * $this->hdRatio, 'y' => $j + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[8 * $this->hdRatio][$j][$faceName]))
                {
                    $volume_points[4 * $this->hdRatio][$j][$faceName] = new Point(['x' => 4 * $this->hdRatio + 4 * $this->hdRatio, 'y' => $j + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 9 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 5 * $this->hdRatio; $faceName++)
            {
                if (!isset($volume_points[$i][0][$faceName]))
                {
                    $volume_points[$i][0][$faceName] = new Point(['x' => $i + 4 * $this->hdRatio, 'y' => 0 + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
                if (!isset($volume_points[$i][12 * $this->hdRatio][$faceName]))
                {
                    $volume_points[$i][12 * $this->hdRatio][$faceName] = new Point(['x' => $i + 4 * $this->hdRatio, 'y' => 12 * $this->hdRatio + 20 * $this->hdRatio, 'z' => $faceName], $this->alpha, $this->omega);
                }
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($j = 0; $j < 12 * $this->hdRatio; $j++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, (16 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['leftLeg']['back'][] = new Polygon([
                    $volume_points[$i][$j][0],
                    $volume_points[$i + 1][$j][0],
                    $volume_points[$i + 1][$j + 1][0],
                    $volume_points[$i][$j + 1][0],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 20 * $this->hdRatio + $j));
                $polygons['leftLeg']['front'][] = new Polygon([
                    $volume_points[$i][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j][4 * $this->hdRatio],
                    $volume_points[$i + 1][$j + 1][4 * $this->hdRatio],
                    $volume_points[$i][$j + 1][4 * $this->hdRatio],
                ], $rgba);
            }
        }
        for ($j = 0; $j < 12 * $this->hdRatio; $j++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 0 + ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['leftLeg']['right'][] = new Polygon([
                    $volume_points[0][$j][$faceName],
                    $volume_points[0][$j][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName + 1],
                    $volume_points[0][$j + 1][$faceName],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, (12 * $this->hdRatio - 1) - ((4 * $this->hdRatio - 1) - $faceName), 20 * $this->hdRatio + $j));
                $polygons['leftLeg']['left'][] = new Polygon([
                    $volume_points[4 * $this->hdRatio][$j][$faceName],
                    $volume_points[4 * $this->hdRatio][$j][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName + 1],
                    $volume_points[4 * $this->hdRatio][$j + 1][$faceName],
                ], $rgba);
            }
        }
        for ($i = 0; $i < 4 * $this->hdRatio; $i++)
        {
            for ($faceName = 0; $faceName < 4 * $this->hdRatio; $faceName++)
            {
                $rgba = new RGBA(imagecolorat($this->skinSource, 4 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), 16 * $this->hdRatio + $faceName));
                $polygons['leftLeg']['top'][] = new Polygon([
                    $volume_points[$i][0][$faceName],
                    $volume_points[$i + 1][0][$faceName],
                    $volume_points[$i + 1][0][$faceName + 1],
                    $volume_points[$i][0][$faceName + 1],
                ], $rgba);

                $rgba = new RGBA(imagecolorat($this->skinSource, 8 * $this->hdRatio + ((4 * $this->hdRatio - 1) - $i), (20 * $this->hdRatio - 1) - $faceName));
                $polygons['leftLeg']['bottom'][] = new Polygon([
                    $volume_points[$i][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName],
                    $volume_points[$i + 1][12 * $this->hdRatio][$faceName + 1],
                    $volume_points[$i][12 * $this->hdRatio][$faceName + 1],
                ], $rgba);
            }
        }
    }
    
    private function determinePolygons(): array
    {
        $polygons = [
            'helmet' => self::CUBE_FACES_FORMAT,
            'head' => self::CUBE_FACES_FORMAT,
            'torso' => self::CUBE_FACES_FORMAT,
            'rightArm' => self::CUBE_FACES_FORMAT,
            'leftArm' => self::CUBE_FACES_FORMAT,
            'rightLeg' => self::CUBE_FACES_FORMAT,
            'leftLeg' => self::CUBE_FACES_FORMAT,
        ];

        $this->determineHeadPolygons($polygons);
        $this->determineHairPolygons($polygons);
        $this->determineTorsoPolygons($polygons);
        $this->determineRightArmPolygons($polygons);
        $this->determineLeftArmPolygons($polygons);
        $this->determineRightLegPolygons($polygons);
        $this->determineLeftLegPolygons($polygons);

        return $polygons;
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
            $cube_max_depth_faces = $cubePoints[0];

            foreach ($cubePoints as $cubePoint)
            {
                $cubePoint->getPoint()->preProject(0, 0, 0, $parts_angles[$faceName]['cos_alpha'], $parts_angles[$faceName]['sin_alpha'], $parts_angles[$faceName]['cos_omega'], $parts_angles[$faceName]['sin_omega']);
                $cubePoint->getPoint()->project();

                if ($cube_max_depth_faces->getPoint()->getDepth() > $cubePoint->getPoint()->getDepth())
                {
                    $cube_max_depth_faces = $cubePoint;
                }
            }
            $faceFormat['back'] = $cube_max_depth_faces->getPlaces();
            $faceFormat['front'] = array_diff(self::ALL_FACES, $faceFormat['back']);
        }

        return $visible_faces;
    }
}