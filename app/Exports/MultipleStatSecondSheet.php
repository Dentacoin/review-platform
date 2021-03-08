<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;     // Automatically register event listeners
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class MultipleStatSecondSheet implements FromArray, WithTitle, WithEvents, WithDrawings, WithColumnFormatting {

    protected $info;
    protected $name;

    public function __construct(array $info, $name, $rows_count) {
        $this->info = $info;
        $this->name = $name;
        $this->rows_count = $rows_count;
    }

    public function title(): string {
        return $this->name;
    }

    public function array(): array {
        return $this->info;
    }

    public function registerEvents(): array {
        return [

            AfterSheet::class => function(AfterSheet $event) {
            // Merge Cells
                $event->sheet->getDelegate()->setMergeCells(['A2:Z2', 'A1:Z1']);

               // freeze the pane
               //$event->sheet->getDelegate()->freezePane('A4');

               // Set the cell content centered
                $event->sheet->getDelegate()->getStyle('A1:Z1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

               // Define the column width
               //$widths = ['A' => 20, 'B' => 15, 'C' => 25];
                $widths = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
                foreach ($widths as $k => $v) {
                // Set the column width
                    if($v == 'A') {
                        $event->sheet->getDelegate()->getColumnDimension($v)->setWidth(30);
                    } else {
                        $event->sheet->getDelegate()->getColumnDimension($v)->setWidth(10);
                    }
                    
                }

                // $event->sheet->getStyle('A3:Z3')->getAlignment()->setWrapText(true);
                // $event->sheet->getStyle('A4:Z4')->getAlignment()->setWrapText(true);
                // $event->sheet->getStyle('A3:C3')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('A1:A500')->getAlignment()->setWrapText(true);

                $heights = [2 => 50, 1 => 50];
                foreach ($heights as $k => $v) {
                // Set the column width
                    $event->sheet->getDelegate($k)->getRowDimension($k)->setRowHeight($v);
                }
               // Other style requirements (set border, background color, etc.) handle the macro given in the extension, you can also customize the macro to achieve, see the official website for details

                // $event->sheet->setColumnFormat(array(
                //     'C' =>  NumberFormat::FORMAT_TEXT,
                // ));

                $event->sheet->getStyle('A2:Z2')->applyFromArray([
                    'font' => [
                        'size' => 14,
                    ]
                ]);

                // $sheet->getStyle('B1')->getFont()->setSize(20);

                $event->sheet->getStyle('A3:Z3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                // dd($this->rows_count);

                if( !empty($this->rows_count) ) {

                    for($i=0; $i < 20; $i++) {
                        $groupStart = ( 2 + ($this->rows_count+4)*$i );
                        $event->sheet->getStyle('A'.($groupStart+1))->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ]
                        ]);
                        $event->sheet->getStyle('A'.($groupStart+2))->applyFromArray([
                            'font' => [
                                'italic' => true
                            ]
                        ]);
                        $event->sheet->getStyle('A'.($groupStart+3))->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ]
                        ]);
                    }
                }

                $event->sheet->getDelegate()->getStyle('A2:Z2')->getAlignment()->applyFromArray( array('vertical' => 'center'));
                $event->sheet->getDelegate()->getStyle('A3:Z3')->getAlignment()->applyFromArray( array('vertical' => 'center'));
            },
        ];
    }

    public function columnFormats(): array {
        return [
            'C' => NumberFormat::FORMAT_PERCENTAGE_00,
            'E' => NumberFormat::FORMAT_PERCENTAGE_00,
            'G' => NumberFormat::FORMAT_PERCENTAGE_00,
            'I' => NumberFormat::FORMAT_PERCENTAGE_00,
            'K' => NumberFormat::FORMAT_PERCENTAGE_00,
            'M' => NumberFormat::FORMAT_PERCENTAGE_00,
            'O' => NumberFormat::FORMAT_PERCENTAGE_00,
            'Q' => NumberFormat::FORMAT_PERCENTAGE_00,
            'S' => NumberFormat::FORMAT_PERCENTAGE_00,
            'U' => NumberFormat::FORMAT_PERCENTAGE_00,
            'W' => NumberFormat::FORMAT_PERCENTAGE_00,
            'Y' => NumberFormat::FORMAT_PERCENTAGE_00,

            'AA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'AY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'BA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'BY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'CA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'CY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'DA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'DY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'EA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'ES' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'EY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'FA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'FY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'GA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'GY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'HA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'HY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'IA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'II' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'IY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'JA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'JY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'KA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'KY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'LA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'LY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'MA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'ME' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'MY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'NA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'NY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'OA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'OY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'PA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'PY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'QA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'QY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'RA' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RC' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RE' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RG' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RI' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RK' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RM' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RO' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RQ' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RS' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RU' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RW' => NumberFormat::FORMAT_PERCENTAGE_00,
            'RY' => NumberFormat::FORMAT_PERCENTAGE_00,

            'SA' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }

    public function drawings() {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('/new-vox-img/logo-vox.png'));
        $drawing->setHeight(34);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(15);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

}