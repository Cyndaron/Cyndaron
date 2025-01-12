<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

enum RegistrationApprovalStatus : int
{
    case UNDECIDED = 0;
    case APPROVED = 1;
    case DISAPPROVED = 2;
}
