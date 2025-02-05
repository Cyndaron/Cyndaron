<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\ContestMember;
use Cyndaron\Geelhoed\Graduation\GraduationRepository;
use Cyndaron\Page\Page;

final class EditSubscriptionPage extends Page
{
    public function __construct(ContestMember $contestMember, GraduationRepository $graduationRepository)
    {
        $this->title = 'Inschrijving wijzigen';
        $graduations = [];
        foreach ($graduationRepository->fetchAllBySport($contestMember->contest->sport) as $graduation)
        {
            $graduations[$graduation->id] = $graduation->name;
        }

        $this->addTemplateVars([
            'contestMember' => $contestMember,
            'graduations' => $graduations,
        ]);
    }
}
