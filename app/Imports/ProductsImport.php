<?php

namespace App\Imports;

use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
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
            $line = $index + 2; // heading row = 1
            $data = [
                'sku' => trim((string) ($row['sku'] ?? '')),
                'barcode' => $this->nullableString($row['barcode'] ?? null),
                'name' => trim((string) ($row['name'] ?? $row['nama'] ?? '')),
                'description' => $this->nullableString($row['description'] ?? $row['deskripsi'] ?? null),
                'unit' => trim((string) ($row['unit'] ?? $row['satuan'] ?? 'PCS')),
                'category' => $this->nullableString($row['category'] ?? $row['kategori'] ?? null),
                'min_stock' => (int) ($row['min_stock'] ?? $row['min'] ?? 0),
                'is_active' => $this->toBool($row['is_active'] ?? $row['aktif'] ?? true),
            ];

            if ($data['sku'] === '' && $data['name'] === '') {
                $this->skipped++;
                continue;
            }

            $existing = Product::where('sku', $data['sku'])->first();

            $rules = [
                'sku' => [
                    'required', 'string', 'max:50',
                    $existing && $this->updateExisting
                        ? Rule::unique('products', 'sku')->ignore($existing->id)
                        : Rule::unique('products', 'sku'),
                ],
                'barcode' => [
                    'nullable', 'string', 'max:50',
                    $existing && $this->updateExisting
                        ? Rule::unique('products', 'barcode')->ignore($existing->id)
                        : Rule::unique('products', 'barcode'),
                ],
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'unit' => 'required|string|max:20',
                'category' => 'nullable|string|max:100',
                'min_stock' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
            ];

            // If existing and not updating — skip as duplicate
            if ($existing && ! $this->updateExisting) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: SKU {$data['sku']} sudah ada (lewati).";
                continue;
            }

            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                $this->skipped++;
                $this->errors[] = "Baris {$line}: " . implode('; ', $validator->errors()->all());
                continue;
            }

            if ($existing && $this->updateExisting) {
                $before = $existing->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active']);
                $existing->update($data);
                $this->updated++;
                AuditLog::log(
                    'master.product.import_update',
                    Product::class,
                    $existing->id,
                    "Import update produk {$existing->sku}",
                    $before,
                    $existing->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active'])
                );
            } else {
                $product = Product::create($data);
                $this->created++;
                AuditLog::log(
                    'master.product.import_create',
                    Product::class,
                    $product->id,
                    "Import create produk {$product->sku}",
                    null,
                    $product->only(['sku', 'barcode', 'name', 'unit', 'category', 'min_stock', 'is_active'])
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
