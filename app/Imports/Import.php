<?php

namespace App\Imports;

use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class Import implements ToModel {
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row) {
        return $row;
    }
}