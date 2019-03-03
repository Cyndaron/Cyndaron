<?php
namespace Cyndaron\Concerts;

use Cyndaron\Page;

class VerwerkBestellingPagina extends Page
{
    public function __construct(string $title, string $message)
    {
        parent::__construct($title);
        $this->showPrePage();
        echo $message;
        $this->showPostPage();
    }
}