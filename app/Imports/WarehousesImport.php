<?php

namespace App\Imports;

use App\Models\AuditLog;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WarehousesImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;

    public int $updated = 0;

    public int $skipped = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(protected bool $updateExisting = false)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = [
                'code' => strtoupper(trim((string) ($row['code'] ?? $row['kode'] ?? ''))),
                'name' => trim((string) ($row['name'] ?? $row['nama'] ?? '')),
                'description' => $this->nullableString($row['description'] ?? $row['deskripsi'] ?? null),
                'location' => $this->nullableString($row['location'] ?? $row['lokasi'] ?? null),
                'is_active' => $this->toBool($row['is_active'] ?? $row['aktif'] ?? true),
            ];

            if ($data['code'] === '' && $data['name'] === '') {
                $this->skipped++;
                continue;
            }

            $existing = Warehouse::where('code', $data['code'])->first();
            if ($existing && ! $this->updateExisting) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: kode {$data['code']} sudah ada (lewati).";
                continue;
            }

            $rules = [
                'code' => [
                    'required', 'string', 'max:20',
                    $existing && $this->updateExisting
                        ? Rule::unique('warehouses', 'code')->ignore($existing->id)
                        : Rule::unique('warehouses', 'code'),
                ],
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'location' => 'nullable|string|max:255',
                'is_active' => 'boolean',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: " . implode('; ', $validator->errors()->all());
                continue;
            }

            if ($existing && $this->updateExisting) {
                $before = $existing->only(['code', 'name', 'description', 'location', 'is_active']);
                $existing->update($data);
                $this->updated++;
                AuditLog::log(
                    'master.warehouse.import_update',
                    Warehouse::class,
                    $existing->id,
                    "Import update gudang {$existing->code}",
                    $before,
                    $existing->only(['code', 'name', 'description', 'location', 'is_active'])
                );
            } else {
                $warehouse = Warehouse::create($data);
                $this->created++;
                AuditLog::log(
                    'master.warehouse.import_create',
                    Warehouse::class,
                    $warehouse->id,
                    "Import create gudang {$warehouse->code}",
                    null,
                    $warehouse->only(['code', 'name', 'description', 'location', 'is_active'])
                );
            }
        }
    }

    protected function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }

    protected function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $s = strtolower(trim((string) $value));
        if (in_array($s, ['0', 'false', 'no', 'n', 'tidak', 'nonaktif', 'inactive'], true)) {
            return false;
        }

        return true;
    }
}
