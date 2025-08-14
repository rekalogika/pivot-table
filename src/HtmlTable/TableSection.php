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

/**
 * Represents a HTML tag containing rows, including table header, table body, or
 * table footer (<thead>, <tbody>, or <tfoot>)
 */
interface TableSection extends RowGroup, Element {}
