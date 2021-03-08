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