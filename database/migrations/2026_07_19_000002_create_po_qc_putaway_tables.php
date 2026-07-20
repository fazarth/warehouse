<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 40)->unique();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 20)->default('ordered'); // draft|ordered|partial|received|cancelled
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'order_date']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity_ordered');
            $table->unsignedInteger('quantity_received')->default(0);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_order_id', 'product_id']);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->after('supplier_id')
                ->constrained('purchase_orders')->nullOnDelete();
            $table->string('qc_status', 20)->default('pending')->after('status'); // pending|passed|failed|partial|skipped
            $table->foreignId('qc_by')->nullable()->after('qc_status')->constrained('users')->nullOnDelete();
            $table->timestamp('qc_at')->nullable()->after('qc_by');
        });

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')->nullable()->after('goods_receipt_id')
                ->constrained('purchase_order_items')->nullOnDelete();
            $table->unsignedInteger('qty_pass')->nullable()->after('quantity');
            $table->unsignedInteger('qty_fail')->nullable()->after('qty_pass');
            $table->string('qc_notes')->nullable()->after('qty_fail');
        });

        Schema::create('putaway_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number', 40)->unique();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_receipt_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('suggested_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete(); // actual bin
            $table->string('batch_number', 50)->nullable();
            $table->string('lot_number', 50)->nullable();
            $table->date('expired_date')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_done')->default(0);
            $table->string('status', 20)->default('pending'); // pending|done|cancelled
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'warehouse_id']);
            $table->index('goods_receipt_id');
        });

        // Ledger type for bin putaway moves
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN type ENUM('receipt','issue','transfer','adjustment','stockopname','putaway') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_mutations MODIFY COLUMN type ENUM('receipt','issue','transfer','adjustment','stockopname') NOT NULL");

        Schema::dropIfExists('putaway_tasks');

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_item_id');
            $table->dropColumn(['qty_pass', 'qty_fail', 'qc_notes']);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropConstrainedForeignId('qc_by');
            $table->dropColumn(['qc_status', 'qc_at']);
        });

        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
