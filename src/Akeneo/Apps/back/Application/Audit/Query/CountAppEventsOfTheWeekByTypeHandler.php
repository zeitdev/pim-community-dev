<?php
declare(strict_types=1);

namespace Akeneo\Apps\Application\Audit\Query;

use Akeneo\Apps\Domain\Audit\Persistence\Query\SelectAppEventsCountByTypeQuery;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CountAppEventsOfTheWeekByTypeHandler
{
    /** @var SelectAppEventsCountByTypeQuery */
    private $selectEventsByWeek;

    public function __construct(SelectAppEventsCountByTypeQuery $selectEventsByWeek)
    {
        $this->selectEventsByWeek = $selectEventsByWeek;
    }

    public function handle(CountAppEventsOfTheWeekByTypeQuery $query)
    {
        $weekEventsCount = $this
            ->selectEventsByWeek
            ->execute($query->eventType(), $query->startDate(), $query->endDate());

        return $weekEventsCount;
    }
}
