<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Geelhoed\Graduation\GraduationRepository;
use Cyndaron\Geelhoed\Graduation\MemberGraduation;
use Cyndaron\Geelhoed\Graduation\MemberGraduationRepository;
use Cyndaron\Geelhoed\Hour\HourRepository;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserRepository;
use Cyndaron\Util\Util;
use PDOException;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function array_map;
use function array_merge;
use function assert;
use function implode;
use function sprintf;

final class MemberController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly MemberRepository $memberRepository,
        private readonly SportRepository $sportRepository,
    ) {
    }

    #[RouteAttribute('get', RequestMethod::GET, UserLevel::ADMIN, isApiMethod: true)]
    public function get(QueryBits $queryBits, MemberRepository $memberRepository, MemberGraduationRepository $mgr): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $ret = [];

        $member = $this->memberRepository->fetchById($id);
        if ($member !== null)
        {
            $ret = array_merge($member->asArray(), $member->profile->asArray());
            $dob = $member->profile->dateOfBirth;
            if ($dob !== null)
            {
                $ret['dateOfBirth'] = $dob->format(Util::SQL_DATE_FORMAT);
            }

            foreach ($memberRepository->getHours($member) as $hour)
            {
                $ret["hour-{$hour->id}"] = true;
            }

            $list = [];
            foreach ($mgr->fetchAllByMember($member) as $memberGraduation)
            {
                $graduation = $memberGraduation->graduation;
                $description = "{$graduation->sport->name}: {$graduation->name} ({$memberGraduation->date})";
                $list[] = sprintf('<li id="member-graduation-%d">%s <a class="btn btn-sm btn-danger remove-member-graduation" data-id="%d"><span class="glyphicon glyphicon-trash"></span></a></li>', $memberGraduation->id, $description, $memberGraduation->id);
            }

            $ret['graduationList'] = implode($list);
        }

        return new JsonResponse($ret);
    }

    #[RouteAttribute('getGrid', RequestMethod::GET, UserLevel::ADMIN, isApiMethod: true)]
    public function getGrid(MemberRepository $memberRepository): JsonResponse
    {
        $grid = new PageManagerMemberGrid($memberRepository, $this->sportRepository);
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
    public function save(RequestParameters $post, MemberGraduationRepository $mgr, HourRepository $hourRepository, GraduationRepository $graduationRepository, UserRepository $userRepository): JsonResponse
    {
        $memberId = $post->getInt('id');
        $isExistingMember = ($memberId > 0);

        if ($isExistingMember)
        {
            $member = $this->memberRepository->fetchById($memberId);
            if ($member === null)
            {
                throw new \Exception('Member not found!');
            }
            $user = $member->profile;
        }
        else
        {
            $user = new User();
            $user->level = UserLevel::LOGGED_IN;
            $user->password = '';
            $member = new Member();
        }

        $user = $this->updateUserFields($user, $post);
        $userRepository->save($user);

        $member = $this->updateMemberFields($user, $member, $post);
        try
        {
            $this->memberRepository->save($member);
        }
        catch (PDOException)
        {
            return new JsonResponse(['error' => 'Error saving member record!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $newGraduationId = $post->getInt('new-graduation-id');
        $newGraduation = $graduationRepository->fetchById($newGraduationId);
        assert($newGraduation !== null);
        $newGraduationDate = $post->getDate('new-graduation-date');
        if ($newGraduationId && $newGraduationDate)
        {
            $mg = new MemberGraduation();
            assert($member->id !== null);
            $mg->member = $member;
            $mg->graduation = $newGraduation;
            $mg->date = $newGraduationDate;
            $mgr->save($mg);
        }

        $hours = [];
        foreach ($hourRepository->fetchAll() as $hour)
        {
            if ($post->getBool("hour-{$hour->id}"))
            {
                $hours[] = $hour;
            }
        }
        $this->memberRepository->setHours($member, $hours);

        // Needs to be done _after_ setting hours.

        $membersToUpdateGridFor = $this->memberRepository->rebuildMonthlyFeeCacheForMember($member);
        $grid = new PageManagerMemberGrid($this->memberRepository, $this->sportRepository);
        $allSports = $this->sportRepository->fetchAll();

        $memberIds = array_map(static function(Member $member)
        {
            return (int)$member->id;
        }, $membersToUpdateGridFor);
        $grid->deleteByMemberIds(...$memberIds);

        $updatedGridItems = [];
        foreach ($membersToUpdateGridFor as $member)
        {
            $gridItem = PageManagerMemberGridItem::createFromMember($this->memberRepository, $member, $allSports);
            $grid->addItem($gridItem);
            $updatedGridItems[] = $gridItem;
        }

        return new JsonResponse($updatedGridItems);
    }

    /**
     * @param User $user
     * @param RequestParameters $post
     *@throws \Safe\Exceptions\PcreException|\Safe\Exceptions\DatetimeException
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
        $member->profile = $user;
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
    public function delete(QueryBits $queryBits, GenericRepository $repository, PageManagerMemberGrid $grid): JsonResponse
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }

        // This is safe to do even if the member does not exist, and may get rid of ghost entries in some cases.
        $grid->deleteByMemberIds($id);

        $member = $this->memberRepository->fetchById($id);
        if ($member === null)
        {
            return new JsonResponse(['error' => 'Member does not exist!'], Response::HTTP_BAD_REQUEST);
        }

        $this->memberRepository->delete($member);
        $repository->deleteById(User::class, (int)$member->profile->id);

        return new JsonResponse();
    }

    #[RouteAttribute('directDebitList', RequestMethod::GET, UserLevel::ADMIN)]
    public function directDebitList(MemberRepository $memberRepository): Response
    {
        $directDebits = DirectDebit::load($memberRepository);
        $page = new DirectDebitListPage($directDebits, $memberRepository);
        return $this->pageRenderer->renderResponse($page);
    }
}
