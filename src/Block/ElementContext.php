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

namespace Rekalogika\PivotTable\Block;

final readonly class ElementContext
{
    public static function createFlat(): self
    {
        return new self(
            depth: 0,
            generatingBlock: null,
            subtotalDepth: 0,
        );
    }

    /**
     * @param int<0,max> $depth
     * @param int<0,max> $subtotalDepth
     */
    public function __construct(
        private int $depth,
        private ?Block $generatingBlock,
        private int $subtotalDepth,
    ) {}

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getGeneratingBlock(): ?Block
    {
        return $this->generatingBlock;
    }

    public function getSubtotalDepth(): int
    {
        return $this->subtotalDepth;
    }
}
