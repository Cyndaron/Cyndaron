<?php
namespace Cyndaron\RegistrationSbk;

use Cyndaron\Template\Template;

class Util extends \Cyndaron\Util
{
    /** @noinspection PhpUnused */
    public static function drawPageManagerTab(): string
    {
        $templateVars = ['events' => Event::fetchAll()];
        return (new Template())->render('RegistrationSbk/PageManagerTab', $templateVars);
    }
}