<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\Page\Page;

class DirectDebitListPage extends Page
{
    /**
     * @param DirectDebit[] $directDebits
     */
    public function __construct(array $directDebits, MemberRepository $memberRepository)
    {
        $this->title = 'Incassolijst';
        $this->addTemplateVars([
            'directDebits' => $directDebits,
            'memberRepository' => $memberRepository,
        ]);
    }
}
