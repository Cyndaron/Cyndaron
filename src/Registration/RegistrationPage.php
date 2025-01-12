<?php
namespace Cyndaron\Registration;

use Cyndaron\Page\Page;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\KnownShortCodes;
use Cyndaron\Util\Setting;
use function file_exists;
use function Safe\date;

final class RegistrationPage extends Page
{
    public function __construct(Event $event)
    {
        $this->title = 'Aanmelding: ' . $event->name;

        $this->addScript('/src/Registration/js/RegistrationPage.js');
        $this->addCss('/src/Registration/css/RegistrationPage.css');
        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');

        $shortCode = Setting::get(BuiltinSetting::SHORT_CODE);

        if ($shortCode === KnownShortCodes::VOV)
        {
            $template = 'Registration/RegistrationPageVOV';
            if (file_exists(__DIR__ . '/templates/RegistrationPageVOV-' . $event->id . '.blade.php'))
            {
                $template = 'Registration/RegistrationPageVOV-' . $event->id;
            }

            $this->template = $template;
            $this->addScript('/src/Registration/js/RegistrationPageVOV.js');

            $ageRanges = [0 => 'Maak een keuze'];
            foreach (Util::getAgeRanges($event) as $ageRange)
            {
                $key = (int)date('Y') - $ageRange[0];
                if ($ageRange[0] === 0)
                {
                    $ageRanges[$key] = "t/m {$ageRange[1]}";
                }
                elseif ($ageRange[1] === INF)
                {
                    $ageRanges[$key] = "{$ageRange[0]}+";
                }
                else
                {
                    $ageRanges[$key] = "$ageRange[0] - $ageRange[1]";
                }
            }

            $this->addTemplateVar('ageRanges', $ageRanges);
        }

        $this->addTemplateVars([
            'event' => $event,
            'ticketTypes' => EventTicketType::loadByEvent($event),
        ]);
    }
}
