<?php
namespace Cyndaron\Registration;

use Cyndaron\Page\Page;
use Cyndaron\Util\Setting;
use Phan\Library\Set;
use function file_exists;
use function Safe\date;

final class RegistrationPage extends Page
{
    public function __construct(Event $event)
    {
        parent::__construct('Aanmelding: ' . $event->name);

        $this->addScript('/src/Registration/js/RegistrationPage.js');
        $this->addCss('/src/Registration/css/RegistrationPage.css');
        $this->addCss('/src/Ticketsale/css/Ticketsale.min.css');

        $organisation = Setting::get(Setting::ORGANISATION);

        if ($organisation === Setting::VALUE_ORGANISATION_VOV || $organisation === Setting::VALUE_ORGANISATION_ZCK)
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
        elseif ($organisation === 'Stichting Bijzondere Koorprojecten')
        {
            $this->template = 'Registration/RegistrationPageSBK';
        }

        $this->addTemplateVars([
            'event' => $event,
            'ticketTypes' => EventTicketType::loadByEvent($event),
        ]);
    }
}
