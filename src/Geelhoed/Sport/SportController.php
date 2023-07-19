<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Sport;

use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SportController extends Controller
{
    protected array $apiPostRoutes = [
        'edit' => ['level' => UserLevel::ADMIN, 'function' => 'apiEdit'],
    ];

    protected function apiEdit(QueryBits $queryBits, RequestParameters $requestParameters): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $sport = Sport::fetchById($id);
        if ($sport === null)
        {
            return new JsonResponse(['error' => 'Sport not found!'], Response::HTTP_NOT_FOUND);
        }

        $sport->name = $requestParameters->getSimpleString('name');
        $sport->juniorFee = $requestParameters->getFloat('juniorFee');
        $sport->seniorFee = $requestParameters->getFloat('seniorFee');

        if (!$sport->save())
        {
            return new JsonResponse(['error' => 'Could not save!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([]);
    }
}
