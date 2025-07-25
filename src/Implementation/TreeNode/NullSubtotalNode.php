<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/analytics package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\PivotTable\Implementation\TreeNode;

use Rekalogika\PivotTable\Contracts\Tree\SubtotalNode;

final readonly class NullSubtotalNode implements SubtotalNode
{
    public function __construct(
        private string $name,
        private mixed $legend,
        private mixed $item,
    ) {}

    public static function fromInterface(SubtotalNode $node): self
    {
        return new self(
            name: $node->getKey(),
            legend: $node->getLegend(),
            item: $node->getItem(),
        );
    }

    #[\Override]
    public function getKey(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getLegend(): mixed
    {
        return $this->legend;
    }

    #[\Override]
    public function getItem(): mixed
    {
        return $this->item;
    }

    #[\Override]
    public function getValue(): mixed
    {
        return null;
    }
}
