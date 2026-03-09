<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * ChunkReadFilter
 *
 * Tells PhpSpreadsheet to only parse a specific row window on each load.
 * Row 1 (the header) is always included so column mapping works correctly.
 *
 * Usage:
 *   $filter = new ChunkReadFilter();
 *   $reader->setReadFilter($filter);
 *
 *   for ($start = 2; $start <= $total + 1; $start += $chunkSize) {
 *       $filter->setRows($start, $chunkSize);
 *       $spreadsheet = $reader->load($filePath);
 *       // … process rows $start … $start + $chunkSize - 1 …
 *       $spreadsheet->disconnectWorksheets();
 *       unset($spreadsheet);
 *       gc_collect_cycles();
 *   }
 */
class ChunkReadFilter implements IReadFilter
{
    private int $startRow = 2;
    private int $endRow   = 101;

    /**
     * @param int $startRow  First data row to include (1-based, ≥ 2)
     * @param int $chunkSize Number of data rows to include
     */
    public function setRows(int $startRow, int $chunkSize): void
    {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize - 1;
    }

    /**
     * PhpSpreadsheet calls this for every cell before deciding to parse it.
     * Return true  → parse the cell.
     * Return false → skip it entirely (saves memory and CPU).
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // Row 1 is the header — always needed for column mapping.
        if ($row === 1) {
            return true;
        }

        return $row >= $this->startRow && $row <= $this->endRow;
    }
}