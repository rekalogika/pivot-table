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

namespace Rekalogika\PivotTable\HtmlTable;

use Rekalogika\PivotTable\Block\Block;

/**
 * Represents a HTML element
 */
interface ElementContext
{
    /**
     * @return int<0,max>
     */
    public function getDepth(): int;

    public function getGeneratingBlock(): ?Block;

    /**
     * @return int<0,max>
     */
    public function getSubtotalDepth(): int;
}
