<?php
namespace Cyndaron;

require_once __DIR__ . '/../check.php';
require_once __DIR__ . '/../functies.url.php';

abstract class Bewerk
{
    protected $id;
    protected $type = '';
    protected $returnUrl;

    public function __construct()
    {
        $this->id = geefGetVeilig('id');
        $returnUrl = geefGetVeilig('returnUrl');

        $this->prepare();

        if ($friendlyUrl = geefPostVeilig('friendlyUrl'))
        {
            $unfriendlyUrl = 'toon' . $this->type . '.php?id=' . $this->id;
            $oudeFriendlyUrl = geefFriendlyUrl($unfriendlyUrl);
            verwijderFriendlyUrl($oudeFriendlyUrl);
            maakFriendlyUrl($friendlyUrl, $unfriendlyUrl);
            // Als de friendly URL gebruikt is in het menu moet deze daar ook worden aangepast
            geefEen('UPDATE menu SET link = ? WHERE link = ?', array($friendlyUrl, $oudeFriendlyUrl));
        }
        if (!$returnUrl)
        {
            $returnUrl = $_SESSION['referrer'];
            $returnUrl = strtr($returnUrl, array('&amp;' => '&'));
        }
        header('Location: ' . $returnUrl);
    }

    abstract protected function prepare();
}

