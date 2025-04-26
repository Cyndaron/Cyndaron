<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<RegistrationTicketType>
 */
final class RegistrationTicketTypeRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = RegistrationTicketType::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }

    /**
     * @param Registration $registration
     * @return RegistrationTicketType[]
     */
    public function loadByRegistration(Registration $registration): array
    {
        return $this->fetchAll(['orderId = ?'], [$registration->id]);
    }
}
