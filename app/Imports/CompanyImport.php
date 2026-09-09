<?php

namespace App\Imports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;

class CompanyImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Company([
            'company_name'  => $row['company_name'],
            'status'  => $row['status'],
        ]);
    }
    public function rules(): array
    {
        return [
            'company_name' => Rule::unique('companies', 'company_name'), // Table name, field in your db
        ];
    }
    public function customValidationMessages()
    {
        return [
            'company_name.unique' => 'Custom message',
        ];
    }
}
