<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\Geelhoed\Webshop\WebshopController;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ClubactieController extends Controller
{
    #[RouteAttribute('opgeven', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function opgevenGet(): Response
    {
        $page = new SubscribePage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('opgeven', RequestMethod::POST, UserLevel::ANONYMOUS)]
    public function opgevenPost(RequestParameters $post): Response
    {
        $firstName = $post->getSimpleString('firstName');
        $tussenvoegsel = $post->getSimpleString('tussenvoegsel');
        $lastName = $post->getSimpleString('lastName');
        $email = $post->getEmail('email');

        $subscriber = new Subscriber();
        $subscriber->firstName = $firstName;
        $subscriber->tussenvoegsel = $tussenvoegsel;
        $subscriber->lastName = $lastName;
        $subscriber->email = $email;
        $subscriber->save();

        $page = new SimplePage('Inschrijven Grote Clubactie', 'Je inschrijving is binnen. Bedankt voor je deelname!');
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('confirm-tickets', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: WebshopController::RIGHT_MANAGE)]
    public function confirmTickets(QueryBits $queryBits, RequestParameters $post, UrlInfo $urlInfo): JsonResponse
    {
        $subscriberId = $queryBits->getInt(2);
        $subscriber = Subscriber::fetchById($subscriberId);
        if ($subscriber === null)
        {
            throw new RuntimeException('Entity not found!');
        }

        $confirmedTickets = $post->getInt('num-tickets');
        $subscriber->numSoldTickets = $confirmedTickets;
        $subscriber->soldTicketsAreVerified = true;

        $webshopController = new WebshopController($this->templateRenderer, $this->pageRenderer);
        $webshopController->sendAccountConfirmationMail($urlInfo, $subscriber);

        $subscriber->emailSent = true;
        $subscriber->save();

        return new JsonResponse([
            'status' => 'ok',
        ]);
    }
}
