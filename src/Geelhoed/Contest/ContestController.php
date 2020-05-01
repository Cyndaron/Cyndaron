<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Controller;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class ContestController extends Controller
{
    protected array $getRoutes = [
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
    ];
    protected array $postRoutes = [
        'subscribe' => ['level' => UserLevel::LOGGED_IN, 'function' => 'subscribe'],
    ];

    public function overview(): void
    {
        new OverviewPage();
    }

    public function view(): void
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        if ($contest)
        {
            new ContestViewPage($contest);
        }
        else
        {
            $this->send404('Wedstrijd niet gevonden!');
        }
    }

    public function subscribe(): void
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        if ($contest)
        {
            $member = Member::loadFromLoggedInUser();
            $contestMember = new ContestMember();
            $contestMember->contestId = $contest->id;
            $contestMember->memberId = $member->id;
            $contestMember->graduationId = (int)filter_input(INPUT_POST, 'graduationId', FILTER_SANITIZE_NUMBER_INT);
            $contestMember->weight = (int)filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_INT);
            $contestMember->save();
            header('Location: /contest/view/' . $contest->id);
        }
        else
        {
            $this->send404('Wedstrijd niet gevonden!');
        }
    }
}