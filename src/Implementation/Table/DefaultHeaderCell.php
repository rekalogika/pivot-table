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

namespace Rekalogika\PivotTable\Implementation\Table;

use Rekalogika\PivotTable\Table\HeaderCell;
use Rekalogika\PivotTable\Table\TableVisitor;

final readonly class DefaultHeaderCell extends DefaultCell implements HeaderCell
{
    #[\Override]
    public function accept(TableVisitor $visitor): mixed
    {
        return $visitor->visitHeaderCell($this);
    }

    #[\Override]
    public function getTagName(): string
    {
        return 'th';
    }
}
