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

namespace Rekalogika\PivotTable;

use Rekalogika\PivotTable\Block\Block;
use Rekalogika\PivotTable\Contracts\TreeNode;
use Rekalogika\PivotTable\Table\Cell;
use Rekalogika\PivotTable\Table\DataCell;
use Rekalogika\PivotTable\Table\HeaderCell;
use Rekalogika\PivotTable\Table\Row;
use Rekalogika\PivotTable\Table\RowGroup;
use Rekalogika\PivotTable\Table\Table;
use Rekalogika\PivotTable\Table\TableBody;
use Rekalogika\PivotTable\Table\TableFooter;
use Rekalogika\PivotTable\Table\TableHeader;

/**
 * @api
 */
class TableRenderer
{
    /**
     * @param list<string> $pivotedNodes
     */
    final public function render(
        TreeNode $treeNode,
        array $pivotedNodes = [],
    ): string {
        $block = Block::new($treeNode, $pivotedNodes);

        return $this->renderTable($block->generateTable());
    }

    /**
     * @todo privatize
     */
    public function renderTable(Table $table): string
    {
        if (\count($table->getRows()) === 0) {
            return $this->renderNoData();
        }

        $result = \sprintf('<table %s>', $this->getTableAttributes());

        foreach ($table as $rowGroup) {
            $result .= $this->renderRowGroup($rowGroup);
        }

        return $result . '</table>';
    }

    protected function renderNoData(): string
    {
        return '<p>No Data</p>';
    }

    protected function getTableAttributes(): string
    {
        return '';
    }

    protected function getTableHeaderAttributes(): string
    {
        return '';
    }

    protected function getTableBodyAttributes(): string
    {
        return '';
    }

    protected function getTableFooterAttributes(): string
    {
        return '';
    }

    protected function getTableRowAttributes(): string
    {
        return '';
    }

    protected function getDataCellAttributes(): string
    {
        return '';
    }

    protected function getHeaderCellAttributes(): string
    {
        return '';
    }

    protected function renderRowGroup(RowGroup $rowGroup): string
    {
        if ($rowGroup instanceof TableHeader) {
            return $this->renderHeader($rowGroup);
        } elseif ($rowGroup instanceof TableBody) {
            return $this->renderBody($rowGroup);
        } elseif ($rowGroup instanceof TableFooter) {
            return $this->renderFooter($rowGroup);
        }

        throw new \InvalidArgumentException('Unknown row group type');
    }

    protected function renderHeader(TableHeader $header): string
    {
        return $this->renderRows('thead', $this->getTableHeaderAttributes(), $header);
    }

    protected function renderBody(TableBody $body): string
    {
        return $this->renderRows('tbody', $this->getTableBodyAttributes(), $body);
    }

    protected function renderFooter(TableFooter $footer): string
    {
        return $this->renderRows('tfoot', $this->getTableFooterAttributes(), $footer);
    }

    /**
     * @param 'thead'|'tbody'|'tfoot' $tag
     */
    private function renderRows(string $tag, string $tagAttributes, RowGroup $rows): string
    {
        $result = \sprintf('<%s %s>', $tag, $tagAttributes);

        foreach ($rows as $row) {
            $result .= $this->renderRow($row);
        }

        return $result . \sprintf('</%s>', $tag);
    }

    protected function renderRow(Row $row): string
    {
        $result = \sprintf('<tr %s>', $this->getTableRowAttributes());

        foreach ($row as $cell) {
            $result .= $this->renderCell($cell);
        }

        return $result . '</tr>';
    }

    protected function renderCell(Cell $cell): string
    {
        if ($cell instanceof HeaderCell) {
            $tag = 'th';
            $attributes = ' ' . $this->getHeaderCellAttributes();
        } elseif ($cell instanceof DataCell) {
            $tag = 'td';
            $attributes = ' ' . $this->getDataCellAttributes();
        } else {
            throw new \InvalidArgumentException('Unknown cell type');
        }

        $colspan = $cell->getColumnSpan();
        $rowspan = $cell->getRowSpan();

        $colspanString = $colspan > 1
            ? \sprintf(' colspan="%d"', $colspan) : '';

        $rowspanString = $rowspan > 1
            ? \sprintf(' rowspan="%d"', $rowspan) : '';

        return \sprintf(
            '<%s%s%s%s>%s</%s>',
            $tag,
            $attributes,
            $colspanString,
            $rowspanString,
            $this->renderContent($cell->getContent()),
            $tag,
        );
    }

    protected function renderContent(mixed $content): string
    {
        if (\is_string($content) || \is_int($content) || \is_float($content)) {
            $result = (string) $content;
        } elseif ($content instanceof \BackedEnum) {
            $result = (string) $content->value;
        } elseif ($content instanceof \UnitEnum) {
            $result = $content->name;
        } elseif ($content instanceof \Stringable) {
            $result = (string) $content;
        } elseif (\is_bool($content)) {
            $result = $content ? 'true' : 'false';
        } elseif (\is_array($content)) {
            $result = implode(', ', array_map(fn($item): string => $this->renderContent($item), $content));
        } else {
            $result = get_debug_type($content);
        }

        return htmlspecialchars($result);
    }
}
