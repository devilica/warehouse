<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericReportExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection|array $data) {}

    public function collection(): Collection
    {
        $items = $this->data instanceof Collection ? $this->data : collect($this->data);

        return $items->map(fn ($row) => $row instanceof \Illuminate\Database\Eloquent\Model ? $row->toArray() : (array) $row);
    }

    public function headings(): array
    {
        $first = $this->collection()->first();

        return $first ? array_keys($first) : [];
    }
}