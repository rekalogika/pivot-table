<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/pivot-table package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\PivotTable\TableToCubeAdapter;

use Rekalogika\PivotTable\TableToCubeAdapter\Model\TableToCubeAdapterDimension;

interface IdentityStrategy
{
    /**
     * @param array<string,TableToCubeAdapterDimension> $coordinates
     * @return string
     */
    public function getCoordinatesSignature(array $coordinates): string;

    public function getMemberSignature(mixed $member): string;
}
