<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\DBAL\Connection;
use Cyndaron\Error\ErrorPage;
use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function count;
use function implode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

class VolunteerController extends Controller
{
    #[RouteAttribute('subscribe', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function subscribe(): Response
    {
        $page = new SubscriptionPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('inschrijven-voor-tryout', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function subscribeToTryoutGet(QueryBits $queryBits, CSRFTokenHandler $tokenHandler): Response
    {
        $event = Tryout::fetchById($queryBits->getInt(2));
        if ($event === null)
        {
            return $this->pageRenderer->renderErrorResponse(new ErrorPage('Fout', 'Evenement niet gevonden!'));
        }
        $page = new SubscribeToTryoutPage($event, $tokenHandler);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('inschrijven-voor-tryout', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function subscribeToTryoutPost(QueryBits $queryBits, RequestParameters $post, Connection $db): JsonResponse
    {
        $event = Tryout::fetchById($queryBits->getInt(2));
        if ($event === null)
        {
            return new JsonResponse(['status' => 'error', 'message' => 'Evenement niet gevonden!'], Response::HTTP_BAD_REQUEST);
        }
        $status = $event->getTryoutStatus();
        $name = $post->getSimpleString('name');
        if (empty($name))
        {
            return new JsonResponse(['status' => 'error', 'message' => 'Je hebt je naam niet ingevuld!'], Response::HTTP_BAD_REQUEST);
        }
        $email = $post->getEmail('email');
        if (empty($email))
        {
            return new JsonResponse(['status' => 'error', 'message' => 'Je hebt je e-mailadres niet ingevuld!'], Response::HTTP_BAD_REQUEST);
        }
        $type = $post->getSimpleString('type');
        if (empty($type))
        {
            return new JsonResponse(['status' => 'error', 'message' => 'Je hebt niet opgegeven waarmee je wilt helpen!'], Response::HTTP_BAD_REQUEST);
        }
        $phone = $post->getSimpleString('phone');
        $comments = $post->getSimpleString('comments');
        if ($status->fullTypes[$type])
        {
            return new JsonResponse(['status' => 'full', 'message' => "Wij hebben intussen al voldoende {$type}s. Eventueel kun je je nog als iets anders inschrijven"], Response::HTTP_BAD_REQUEST);
        }

        $rounds = [];
        $alreadyFilledRounds = [];
        for ($i = 0; $i < $event->getTryoutNumRounds(); $i++)
        {
            $on = $post->getBool('round-' . $i);
            if ($on)
            {
                $rounds[] = $i;
                if ($status->fullStatus[$i][$type])
                {
                    $alreadyFilledRounds[] = $i + 1;
                }
            }
        }
        if (count($rounds) === 0)
        {
            return new JsonResponse(['status' => 'error', 'message' => 'Je hebt geen rondes opgegeven waarin je wilt helpen!'], Response::HTTP_BAD_REQUEST);
        }


        $count = count($alreadyFilledRounds);
        if ($count > 0)
        {
            $roundText = $count === 1 ? 'Voor ronde ' : 'Voor rondes ';
            $text = $roundText . implode(' en ', $alreadyFilledRounds) . ' hebben we al genoeg inschrijvingen. Ben je bereid om ons in de andere rondes nog te helpen? Vink dan de volle rondes uit op het formulier en probeer het opnieuw.';
            return new JsonResponse(['status' => 'partially_full', 'message' => $text, 'alreadyFilledRounds' => $alreadyFilledRounds], Response::HTTP_BAD_REQUEST);
        }

        $jsonData = json_encode(['rounds' => $rounds], JSON_THROW_ON_ERROR);

        $sql = 'INSERT INTO geelhoed_volunteer_tot_participation(`eventId`, `name`, `email`, `phone`, `type`, `data`, `comments`) VALUES (?, ?, ?, ?, ?, ?, ?);';
        $db->executeQuery($sql, [$event->id, $name, $email, $phone, $type, $jsonData, $comments]);

        return new JsonResponse([]);
    }
}
