<?php

namespace App\Imports;

use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SuppliersImport implements ToCollection, WithHeadingRow
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
                'address' => $this->nullableString($row['address'] ?? $row['alamat'] ?? null),
                'phone' => $this->nullableString($row['phone'] ?? $row['telepon'] ?? null),
                'email' => $this->nullableString($row['email'] ?? null),
                'contact_person' => $this->nullableString($row['contact_person'] ?? $row['kontak'] ?? null),
                'is_active' => $this->toBool($row['is_active'] ?? $row['aktif'] ?? true),
            ];

            if ($data['code'] === '' && $data['name'] === '') {
                $this->skipped++;
                continue;
            }

            $existing = Supplier::where('code', $data['code'])->first();
            if ($existing && ! $this->updateExisting) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: kode {$data['code']} sudah ada (lewati).";
                continue;
            }

            $rules = [
                'code' => [
                    'required', 'string', 'max:20',
                    $existing && $this->updateExisting
                        ? Rule::unique('suppliers', 'code')->ignore($existing->id)
                        : Rule::unique('suppliers', 'code'),
                ],
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'contact_person' => 'nullable|string|max:255',
                'is_active' => 'boolean',
            ];

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: " . implode('; ', $validator->errors()->all());
                continue;
            }

            if ($existing && $this->updateExisting) {
                $before = $existing->only(['code', 'name', 'address', 'phone', 'email', 'contact_person', 'is_active']);
                $existing->update($data);
                $this->updated++;
                AuditLog::log(
                    'master.supplier.import_update',
                    Supplier::class,
                    $existing->id,
                    "Import update supplier {$existing->code}",
                    $before,
                    $existing->only(['code', 'name', 'address', 'phone', 'email', 'contact_person', 'is_active'])
                );
            } else {
                $supplier = Supplier::create($data);
                $this->created++;
                AuditLog::log(
                    'master.supplier.import_create',
                    Supplier::class,
                    $supplier->id,
                    "Import create supplier {$supplier->code}",
                    null,
                    $supplier->only(['code', 'name', 'address', 'phone', 'email', 'contact_person', 'is_active'])
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
