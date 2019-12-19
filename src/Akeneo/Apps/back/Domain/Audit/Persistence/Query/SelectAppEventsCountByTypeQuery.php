<?php
declare(strict_types=1);

namespace Akeneo\Apps\Domain\Audit\Persistence\Query;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SelectAppEventsCountByTypeQuery
{
    public function execute(string $eventType, string $startDate, string $endDate): array;
}
