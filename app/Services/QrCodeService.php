<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCodeService
{
    /** @var array<string, class-string<Model>> */
    public const ENTITY_MAP = [
        'product' => Product::class,
        'location' => Location::class,
        'warehouse' => Warehouse::class,
        'supplier' => Supplier::class,
    ];

    public function ensureFor(Model $entity, ?string $preferredCode = null, ?string $label = null): QrCode
    {
        $type = $this->entityType($entity);

        $existing = QrCode::query()
            ->where('entity_type', $type)
            ->where('entity_id', $entity->getKey())
            ->first();

        if ($existing) {
            return $existing;
        }

        $code = $preferredCode ?: $this->generateCode($type, $entity);

        return QrCode::create([
            'code' => $code,
            'entity_type' => $type,
            'entity_id' => $entity->getKey(),
            'label' => $label ?: $this->defaultLabel($entity),
            'is_active' => true,
        ]);
    }

    public function resolve(string $code): ?QrCode
    {
        $code = strtoupper(trim($code));

        return QrCode::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    public function resolveEntity(string $code): ?Model
    {
        $qr = $this->resolve($code);
        if (!$qr) {
            return null;
        }

        $class = self::ENTITY_MAP[$qr->entity_type] ?? null;
        if (!$class) {
            return null;
        }

        return $class::query()->find($qr->entity_id);
    }

    public function generateCode(string $type, Model $entity): string
    {
        $prefix = match ($type) {
            'product' => 'ITEM',
            'location' => 'LOC',
            'warehouse' => 'WH',
            'supplier' => 'SUP',
            'batch' => 'BATCH',
            default => strtoupper(Str::limit($type, 4, '')),
        };

        // Prefer business codes when present
        if ($type === 'product' && !empty($entity->sku)) {
            $candidate = 'ITEM-' . Str::upper(preg_replace('/[^A-Za-z0-9]+/', '-', $entity->sku));
            if (!$this->codeExists($candidate)) {
                return $candidate;
            }
        }

        if ($type === 'location' && !empty($entity->code)) {
            $candidate = 'LOC-' . Str::upper($entity->code);
            if (!$this->codeExists($candidate)) {
                return $candidate;
            }
        }

        if ($type === 'warehouse' && !empty($entity->code)) {
            $candidate = Str::upper($entity->code);
            if (!str_starts_with($candidate, 'WH-')) {
                $candidate = 'WH-' . $candidate;
            }
            if (!$this->codeExists($candidate)) {
                return $candidate;
            }
        }

        if ($type === 'supplier' && !empty($entity->code)) {
            $candidate = Str::upper($entity->code);
            if (!$this->codeExists($candidate)) {
                return $candidate;
            }
        }

        do {
            $candidate = sprintf('%s-%06d', $prefix, random_int(1, 999999));
        } while ($this->codeExists($candidate));

        return $candidate;
    }

    protected function codeExists(string $code): bool
    {
        return QrCode::query()->where('code', $code)->exists();
    }

    protected function entityType(Model $entity): string
    {
        foreach (self::ENTITY_MAP as $type => $class) {
            if ($entity instanceof $class) {
                return $type;
            }
        }

        return Str::snake(class_basename($entity));
    }

    protected function defaultLabel(Model $entity): string
    {
        if ($entity instanceof Product) {
            return $entity->name . ' (' . $entity->sku . ')';
        }
        if ($entity instanceof Location) {
            return $entity->name . ' [' . $entity->code . ']';
        }
        if ($entity instanceof Warehouse) {
            return $entity->name;
        }
        if ($entity instanceof Supplier) {
            return $entity->name;
        }

        return (string) ($entity->name ?? $entity->getKey());
    }
}
