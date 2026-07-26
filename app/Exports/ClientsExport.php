<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\FormField;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var Collection<int, FormField> */
    protected Collection $fields;

    public function __construct()
    {
        $this->fields = FormField::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function collection(): Collection
    {
        return Client::query()
            ->with(['latestVisit.fieldValues'])
            ->orderByDesc('id')
            ->get();
    }

    public function headings(): array
    {
        return array_merge(
            ['System ID', 'Visit Date', 'Created At'],
            $this->fields->pluck('label')->all()
        );
    }

    /**
     * @param  Client  $client
     */
    public function map($client): array
    {
        $visit = $client->latestVisit;
        $values = $visit?->fieldValues->keyBy('form_field_id') ?? collect();

        $row = [
            $client->system_id,
            optional($visit?->visited_at)?->toDateTimeString(),
            optional($client->created_at)?->toDateTimeString(),
        ];

        foreach ($this->fields as $field) {
            $row[] = $values->get($field->id)?->value;
        }

        return $row;
    }
}
