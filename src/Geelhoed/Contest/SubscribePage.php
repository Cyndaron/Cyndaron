<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page\Page;

final class SubscribePage extends Page
{
    public function __construct(Contest $contest, Member $member)
    {
        $this->title = "Inschrijven: {$contest->name}";
        $this->addTemplateVars([
            'contest' => $contest,
            'member' => $member,
        ]);
    }
}
