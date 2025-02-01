<?php
namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\MemberGraduationRepository;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\MemberGraduation;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Util;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function sprintf;
use function array_merge;
use function implode;
use function assert;

final class MemberController extends Controller
{
    #[RouteAttribute('get', RequestMethod::GET, UserLevel::ADMIN, isApiMethod: true)]
    public function get(QueryBits $queryBits, MemberGraduationRepository $mgr): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $ret = [];

        $member = Member::fetchById($id);
        if ($member !== null)
        {
            $ret = array_merge($member->asArray(), $member->getProfile()->asArray());
            $dob = $member->getProfile()->dateOfBirth;
            if ($dob !== null)
            {
                $ret['dateOfBirth'] = $dob->format(Util::SQL_DATE_FORMAT);
            }

            foreach ($member->getHours() as $hour)
            {
                $ret["hour-{$hour->id}"] = true;
            }

            $list = [];
            foreach ($mgr->fetchAllByMember($member) as $memberGraduation)
            {
                $graduation = $memberGraduation->graduation;
                $description = "{$graduation->getSport()->name}: {$graduation->name} ({$memberGraduation->date})";
                $list[] = sprintf('<li id="member-graduation-%d">%s <a class="btn btn-sm btn-danger remove-member-graduation" data-id="%d"><span class="glyphicon glyphicon-trash"></span></a></li>', $memberGraduation->id, $description, $memberGraduation->id);
            }

            $ret['graduationList'] = implode($list);
        }

        return new JsonResponse($ret);
    }

    #[RouteAttribute('getGrid', RequestMethod::GET, UserLevel::ADMIN, isApiMethod: true)]
    public function getGrid(): JsonResponse
    {
        $grid = new PageManagerMemberGrid();
        return new JsonResponse($grid->get());
    }

    #[RouteAttribute('removeGraduation', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function removeGraduation(QueryBits $queryBits, MemberGraduationRepository $mgr): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $mgr->deleteById($id);
        $mgr->rebuildByMemberCache();

        return new JsonResponse();
    }

    #[RouteAttribute('save', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function save(RequestParameters $post, MemberGraduationRepository $mgr): JsonResponse
    {
        $memberId = $post->getInt('id');

        // Edit existing
        if ($memberId > 0)
        {
            $member = Member::fetchById($memberId);
            if ($member === null)
            {
                throw new \Exception('Member not found!');
            }
            $user = $member->getProfile();
        }
        else
        {
            $user = new User();
            $user->level = UserLevel::LOGGED_IN;
            $user->password = '';
            $member = new Member();
        }

        $user = $this->updateUserFields($user, $post);
        if (!$user->save())
        {
            return new JsonResponse(['error' => 'Error saving user record!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $member = $this->updateMemberFields($user, $member, $post);
        if (!$member->save())
        {
            return new JsonResponse(['error' => 'Error saving member record!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $newGraduationId = $post->getInt('new-graduation-id');
        $newGraduation = Graduation::fetchById($newGraduationId);
        assert($newGraduation !== null);
        $newGraduationDate = $post->getDate('new-graduation-date');
        if ($newGraduationId && $newGraduationDate)
        {
            $mg = new MemberGraduation();
            assert($member->id !== null);
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $mg->memberId = $member->id;
            $mg->graduation = $newGraduation;
            $mg->date = $newGraduationDate;
            $mgr->save($mg);
        }

        $hours = [];
        foreach (Hour::fetchAll() as $hour)
        {
            if ($post->getBool("hour-{$hour->id}"))
            {
                $hours[] = $hour;
            }
        }
        $member->setHours($hours);
        $grid = new PageManagerMemberGrid();
        $grid->rebuild();

        $gridItem = PageManagerMemberGridItem::createFromMember($member);

        return new JsonResponse($gridItem);
    }

    /**
     * @param User $user
     * @param RequestParameters $post
     * @throws \Safe\Exceptions\PcreException
     * @return User
     */
    private function updateUserFields(User $user, RequestParameters $post): User
    {
        $user->username = $post->getSimpleString('username');
        $user->email = $post->getEmail('email') ?: null;
        $user->firstName = $post->getSimpleString('firstName');
        $user->tussenvoegsel = $post->getTussenvoegsel('tussenvoegsel');
        $user->lastName = $post->getSimpleString('lastName');
        $user->role = $post->getSimpleString('role');
        $user->comments = $post->getHTML('comments');
        // Skipping avatar, hideFromMemberList
        $user->gender = $post->getSimpleString('gender');
        $user->street = $post->getSimpleString('street');
        $user->houseNumber = $post->getInt('houseNumber');
        $user->houseNumberAddition = $post->getSimpleString('houseNumberAddition');
        $user->postalCode = $post->getPostcode('postalCode');
        $user->city = $post->getSimpleString('city');
        $dateOfBirth = $post->getDate('dateOfBirth') ?: null;
        $user->dateOfBirth = $dateOfBirth ? DateTime::createFromFormat(Util::SQL_DATE_FORMAT, $dateOfBirth) : null;
        $user->notes = $post->getHTML('notes');
        $user->optOut = $post->getBool('optOut');

        return $user;
    }

    /**
     * @param User $user
     * @param Member $member
     * @param RequestParameters $post
     * @throws \Safe\Exceptions\PcreException
     * @return Member
     */
    private function updateMemberFields(User $user, Member $member, RequestParameters $post): Member
    {
        assert($user->id !== null);
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $member->userId = $user->id;
        $member->parentEmail = $post->getEmail('parentEmail');
        $member->phoneNumbers = $post->getSimpleString('phoneNumbers');
        $member->isContestant = $post->getBool('isContestant');
        $member->paymentMethod = $post->getSimpleString('paymentMethod');
        $member->iban = $post->getSimpleString('iban');
        $member->ibanHolder = $post->getSimpleString('ibanHolder');
        $member->paymentProblem = $post->getBool('paymentProblem');
        $member->paymentProblemNote = $post->getHTML('paymentProblem');
        $member->freeParticipation = $post->getBool('freeParticipation');
        $member->discount = $post->getFloat('discount');
        $member->temporaryStop = $post->getBool('temporaryStop');
        $joinedAt = $post->getDate('joined');
        if ($joinedAt !== '')
        {
            $member->joinedAt = $joinedAt;
        }
        $member->jbnNumber = $post->getAlphaNum('jbnNumber');
        $member->jbnNumberLocation = $post->getSimpleString('jbnNumberLocation');

        return $member;
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true)]
    public function delete(QueryBits $queryBits, GenericRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $member = Member::fetchById($id);
        $repository->deleteById(Member::class, $id);
        if ($member !== null && $member->userId > 0)
        {
            $repository->deleteById(User::class, $member->userId);
        }

        return new JsonResponse();
    }

    #[RouteAttribute('directDebitList', RequestMethod::GET, UserLevel::ADMIN)]
    public function directDebitList(): Response
    {
        $directDebits = DirectDebit::load();
        $page = new DirectDebitListPage($directDebits);
        return $this->pageRenderer->renderResponse($page);
    }
}
