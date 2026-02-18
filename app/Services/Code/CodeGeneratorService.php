<?php

namespace App\Services\Code;

class CodeGeneratorService
{
    protected CodeGeneratorCore $core;

    public function __construct()
    {
        $this->core = new CodeGeneratorCore();
    }

    /**
     * =====================================================
     * PUBLIC FUNCTIONS (CONTROLLER PAKAI INI)
     * =====================================================
     */

    public function generateMess(): string
    {
        return $this->core->generate(
            keyName: 'mess',
            prefix: 'MES-BDM-'
        );
    }

    public function generateWorkshop(): string
    {
        return $this->core->generate(
            keyName: 'workshop',
            prefix: 'WSP-BDM-'
        );
    }

    public function generateRepair(): string
    {
        return $this->core->generate(
            keyName: 'repair',
            prefix: 'RPR-BDM-'
        );
    }

    // =====================================================
    // TAMBAH MODUL BARU → TINGGAL TAMBAH FUNGSI DI SINI
    // =====================================================
}
