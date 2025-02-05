<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Sport;

use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class SportController
{
    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function apiEdit(QueryBits $queryBits, RequestParameters $requestParameters, SportRepository $sportRepository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $sport = $sportRepository->fetchById($id);
        if ($sport === null)
        {
            return new JsonResponse(['error' => 'Sport not found!'], Response::HTTP_NOT_FOUND);
        }

        $sport->name = $requestParameters->getSimpleString('name');
        $sport->juniorFee = $requestParameters->getFloat('juniorFee');
        $sport->seniorFee = $requestParameters->getFloat('seniorFee');

        $sportRepository->save($sport);

        return new JsonResponse([]);
    }
}
