<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class MultipleSheet implements FromArray, WithTitle {

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

}