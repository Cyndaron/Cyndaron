<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Page\Page;

final class EditSubscriptionPage extends Page
{
    public function __construct(ContestMember $contestMember)
    {
        $this->title = 'Inschrijving wijzigen';
        $graduations = [];
        foreach (Graduation::fetchAllBySport($contestMember->getContest()->getSport()) as $graduation)
        {
            $graduations[$graduation->id] = $graduation->name;
        }

        $this->addTemplateVars([
            'contestMember' => $contestMember,
            'graduations' => $graduations,
        ]);
    }
}
