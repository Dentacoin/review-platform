<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleStatSheetExport implements WithMultipleSheets {

    use Exportable;

    protected $langs;
    
    public function __construct($langs, $rows_count) {
        $this->langs = $langs;
        $this->rows_count = $rows_count;
    }

    public function sheets(): array {
        $sheets = [];

        $i = 0;
        foreach($this->langs as $key => $info) {
            $i++;

            if($i == 1) {
                $sheets[] = new MultipleStatSheet($info, $key );
            } else {
                $sheets[] = new MultipleStatSecondSheet($info, $key, $this->rows_count );
            }
            
        }

        return $sheets;
    }
}