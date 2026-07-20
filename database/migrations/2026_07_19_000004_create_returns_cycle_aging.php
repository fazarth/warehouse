<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // W4 returns + cycle count mode + aging support
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN type ENUM('receipt','issue','transfer','adjustment','stockopname','putaway','return') NOT NULL");

        Schema::create('stock_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 40)->unique();
            $table->string('source', 30)->default('customer'); // customer|supplier|internal
            $table->foreignId('goods_issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->date('return_date');
            $table->string('status', 20)->default('draft'); // draft|received|cancelled
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('batch_number', 50)->nullable();
            $table->date('expired_date')->nullable();
            $table->unsignedInteger('quantity');
            $table->string('condition', 20)->default('good'); // good|damaged|expired
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // Cycle count = stockopname with mode + optional location scope
        if (! Schema::hasColumn('stockopnames', 'mode')) {
            Schema::table('stockopnames', function (Blueprint $table) {
                $table->string('mode', 20)->default('full')->after('status'); // full|cycle
                $table->foreignId('location_id')->nullable()->after('warehouse_id')->constrained('locations')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stockopnames', 'mode')) {
            Schema::table('stockopnames', function (Blueprint $table) {
                $table->dropConstrainedForeignId('location_id');
                $table->dropColumn('mode');
            });
        }

        Schema::dropIfExists('stock_return_items');
        Schema::dropIfExists('stock_returns');

        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN type ENUM('receipt','issue','transfer','adjustment','stockopname','putaway') NOT NULL");
    }
};
