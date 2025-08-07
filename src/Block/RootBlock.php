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

namespace Rekalogika\PivotTable\Block;

use Rekalogika\PivotTable\Contracts\TreeNode;
use Rekalogika\PivotTable\Implementation\Table\DefaultRows;

final class RootBlock extends BranchBlock
{
    protected function __construct(
        TreeNode $node,
        BlockContext $context,
    ) {
        parent::__construct(
            node: $node,
            parent: null,
            context: $context,
        );
    }

    #[\Override]
    public function getHeaderRows(): DefaultRows
    {
        return $this->getChildrenBlockGroup()->getHeaderRows();
    }

    #[\Override]
    public function getDataRows(): DefaultRows
    {
        return $this->getChildrenBlockGroup()->getDataRows();
    }
}
