<?php

namespace App\Utils;

class Meta
{
    public int $currentPage;
    public int $totalPage;

    public function __construct(int $currentPage, int $totalPage)
    {
        $this->currentPage = $currentPage;
        $this->totalPage   = $totalPage;
    }
}
