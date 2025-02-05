<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use function array_fill;
use function count;

/**
 * @implements RepositoryInterface<Tryout>
 */
final class TryoutRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Tryout::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @return array<int, array<string, list<TryoutParticipation>>>
     */
    public function getParticipationData(Tryout $tryout): array
    {
        $numRounds = $tryout->getNumRounds();
        $ret = [];
        for ($i = 0; $i < $numRounds; $i++)
        {
            $ret[$i] = [
                TryoutHelpType::TAFELMEDEWERKER->value => [],
                TryoutHelpType::SCHEIDSRECHTER->value => [],
                TryoutHelpType::GROEPJESBEGELEIDER->value => [],
            ];
        }

        $participations = $this->genericRepository->fetchAll(TryoutParticipation::class, ['eventId = ?'], [$tryout->id]);
        foreach ($participations as $participation)
        {
            $decoded = $participation->getJsonData();
            $type = $participation->type;
            foreach ($decoded['rounds'] as $round)
            {
                $ret[$round][$type][] = $participation;
            }
        }

        return $ret;
    }

    public function getStatus(Tryout $tryout): TryoutStatus
    {
        $participationData = $this->getParticipationData($tryout);
        $neededNumbers = $tryout->getNeededNumbers();
        $fullStatus = array_fill(0, $tryout->getNumRounds(), []);
        $fullRounds = array_fill(0, $tryout->getNumRounds(), true);
        $fullTypes  = [
            TryoutHelpType::TAFELMEDEWERKER->value => true,
            TryoutHelpType::SCHEIDSRECHTER->value => true,
            TryoutHelpType::GROEPJESBEGELEIDER->value => true,
        ];

        foreach ($participationData as $round => $roundData)
        {
            foreach ($roundData as $type => $records)
            {
                $value = false;
                if (count($records) >= $neededNumbers[$round][$type])
                {
                    $value = true;
                }
                else
                {
                    $fullRounds[$round] = false;
                    $fullTypes[$type] = false;
                }

                $fullStatus[$round][$type] = $value;
            }
        }

        return new TryoutStatus($fullStatus, $fullRounds, $fullTypes);
    }
}
