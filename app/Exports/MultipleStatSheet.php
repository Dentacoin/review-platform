<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\WithEvents;     // Automatically register event listeners
use Maatwebsite\Excel\Events\AfterSheet;

use Maatwebsite\Excel\Concerns\WithDrawings;

use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MultipleStatSheet implements FromArray, WithTitle, WithEvents, WithDrawings {

   	protected $info;
   	protected $name;

    public function __construct(array $info, $name) {
        $this->info = $info;
        $this->name = $name;
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

               //, 'A2:C2', 'D2:O2'

               // freeze the pane
               //$event->sheet->getDelegate()->freezePane('A4');

               // Set the cell content centered
               	$event->sheet->getDelegate()->getStyle('A1:Z1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

               // Define the column width
               //$widths = ['A' => 20, 'B' => 15, 'C' => 25];
               	$widths = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
               	foreach ($widths as $k => $v) {
                // Set the column width
                   	$event->sheet->getDelegate()->getColumnDimension($v)->setWidth(20);
               	}

				$event->sheet->getStyle('A3:Z3')->getAlignment()->setWrapText(true);
				$event->sheet->getStyle('A4:Z4')->getAlignment()->setWrapText(true);

				$heights = [2 => 50, 1 => 50];
				foreach ($heights as $k => $v) {
                // Set the column width
                   	$event->sheet->getDelegate($k)->getRowDimension($k)->setRowHeight($v);
               	}
               // Other style requirements (set border, background color, etc.) handle the macro given in the extension, you can also customize the macro to achieve, see the official website for details


               	$event->sheet->getStyle('A2:Z2')->applyFromArray([
                    'font' => [
                        'size' => 14,
                    ]
                ]);

               	$event->sheet->getStyle('A3:Z3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ]
                ]);

               	$event->sheet->getDelegate()->getStyle('A2:Z2')->getAlignment()->applyFromArray( array('vertical' => 'center'));
           	},
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