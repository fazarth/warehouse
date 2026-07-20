<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
                $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->string('code', 50);
                $table->string('name');
                $table->enum('type', ['zone', 'rack', 'bin'])->default('bin');
                $table->unsignedInteger('capacity')->nullable();
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['warehouse_id', 'code']);
                $table->index(['warehouse_id', 'type']);
                $table->index('parent_id');
            });
        }

        if (!Schema::hasTable('qr_codes')) {
            Schema::create('qr_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 80)->unique();
                $table->string('entity_type', 50);
                $table->unsignedBigInteger('entity_id');
                $table->string('label')->nullable();
                $table->json('meta')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['entity_type', 'entity_id']);
                $table->index(['entity_type', 'entity_id']);
            });
        }

        if (Schema::hasTable('product_stocks') && !Schema::hasColumn('product_stocks', 'location_id')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->foreignId('location_id')->nullable()->after('warehouse_id')
                    ->constrained('locations')->nullOnDelete();
                $table->index('location_id');
            });
        }

        $this->swapProductStockUnique();
    }

    public function down(): void
    {
        if (Schema::hasTable('product_stocks') && $this->indexExists('product_stocks', 'product_stocks_product_wh_loc_batch_unique')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->dropUnique('product_stocks_product_wh_loc_batch_unique');
            });
        }

        if (Schema::hasTable('product_stocks') && Schema::hasColumn('product_stocks', 'location_id')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('location_id');
            });
        }

        if (Schema::hasTable('product_stocks') && !$this->indexExists('product_stocks', 'product_stocks_product_id_warehouse_id_batch_number_unique')) {
            Schema::table('product_stocks', function (Blueprint $table) {
                $table->unique(['product_id', 'warehouse_id', 'batch_number']);
            });
        }

        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('locations');
    }

    protected function swapProductStockUnique(): void
    {
        if (!Schema::hasTable('product_stocks')) {
            return;
        }

        $old = 'product_stocks_product_id_warehouse_id_batch_number_unique';
        $new = 'product_stocks_product_wh_loc_batch_unique';

        if ($this->indexExists('product_stocks', $old)) {
            // MySQL may refuse DROP UNIQUE while an FK uses one of its columns as supporting index.
            // Drop FKs that depend on warehouse_id/product_id, swap unique, then restore FKs.
            $this->dropForeignIfExists('product_stocks', 'product_stocks_product_id_foreign');
            $this->dropForeignIfExists('product_stocks', 'product_stocks_warehouse_id_foreign');

            Schema::table('product_stocks', function (Blueprint $table) use ($old) {
                $table->dropUnique($old);
            });

            // Ensure plain indexes remain for FK restore
            if (!$this->indexExists('product_stocks', 'product_stocks_product_id_index')) {
                Schema::table('product_stocks', function (Blueprint $table) {
                    $table->index('product_id');
                });
            }
            if (!$this->indexExists('product_stocks', 'product_stocks_warehouse_id_foreign')
                && !$this->indexExists('product_stocks', 'product_stocks_warehouse_id_index')) {
                Schema::table('product_stocks', function (Blueprint $table) {
                    $table->index('warehouse_id');
                });
            }

            if (!$this->indexExists('product_stocks', $new)) {
                Schema::table('product_stocks', function (Blueprint $table) use ($new) {
                    $table->unique(
                        ['product_id', 'warehouse_id', 'location_id', 'batch_number'],
                        $new
                    );
                });
            }

            // Restore FKs if missing
            $this->addForeignIfMissing('product_stocks', 'product_id', 'products', 'product_stocks_product_id_foreign');
            $this->addForeignIfMissing('product_stocks', 'warehouse_id', 'warehouses', 'product_stocks_warehouse_id_foreign');
        } elseif (!$this->indexExists('product_stocks', $new) && Schema::hasColumn('product_stocks', 'location_id')) {
            Schema::table('product_stocks', function (Blueprint $table) use ($new) {
                $table->unique(
                    ['product_id', 'warehouse_id', 'location_id', 'batch_number'],
                    $new
                );
            });
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index]);

        return count($rows) > 0;
    }

    protected function dropForeignIfExists(string $table, string $fkName): void
    {
        $db = DB::getDatabaseName();
        $exists = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$db, $table, $fkName, 'FOREIGN KEY']
        );

        if ($exists) {
            Schema::table($table, function (Blueprint $blueprint) use ($fkName) {
                $blueprint->dropForeign($fkName);
            });
        }
    }

    protected function addForeignIfMissing(string $table, string $column, string $refTable, string $fkName): void
    {
        $db = DB::getDatabaseName();
        $exists = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$db, $table, $fkName, 'FOREIGN KEY']
        );

        if (!$exists) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $refTable, $fkName) {
                $blueprint->foreign($column, $fkName)->references('id')->on($refTable);
            });
        }
    }
};
