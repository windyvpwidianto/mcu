<?php

namespace App\Imports;

use App\Models\EventSubType;
use Maatwebsite\Excel\Concerns\ToModel;

class EventSubTypeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new EventSubType([
            //
        ]);
    }
}
