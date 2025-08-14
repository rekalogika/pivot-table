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

namespace Rekalogika\PivotTable\Block\Result;

use Rekalogika\PivotTable\HtmlTable\TableFooter;
use Rekalogika\PivotTable\HtmlTable\TableVisitor;

final class DefaultTableFooter extends DefaultTableSection implements TableFooter
{
    #[\Override]
    public function accept(TableVisitor $visitor): mixed
    {
        return $visitor->visitTableFooter($this);
    }

    #[\Override]
    public function getTagName(): string
    {
        return 'tfoot';
    }
}
