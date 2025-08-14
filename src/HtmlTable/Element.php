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
 * Represents a HTML element
 */
interface Element
{
    public function getTagName(): string;

    public function getContext(): mixed;

    /**
     * @template T
     * @param TableVisitor<T> $visitor
     * @return T
     */
    public function accept(TableVisitor $visitor): mixed;
}
