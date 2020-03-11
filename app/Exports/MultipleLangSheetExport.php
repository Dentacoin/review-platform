<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleLangSheetExport implements WithMultipleSheets {

    use Exportable;

    protected $langs;
    
    public function __construct($langs) {
        $this->langs = $langs;
    }

    public function sheets(): array {
        $sheets = [];

        foreach($this->langs as $key => $info) {
            $sheets[] = new MultipleSheet($info, $key );
        }

        return $sheets;
    }
}