<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand GI status pipeline: pending → picking → packing → packed → shipped → delivered
        DB::statement("ALTER TABLE goods_issues MODIFY COLUMN status ENUM('pending','picking','packing','packed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('goods_issues', function (Blueprint $table) {
            $table->string('strategy', 20)->default('fefo')->after('status'); // fefo|fifo|manual
            $table->foreignId('allocated_by')->nullable()->after('shipped_at')->constrained('users')->nullOnDelete();
            $table->timestamp('allocated_at')->nullable()->after('allocated_by');
            $table->foreignId('picked_by')->nullable()->after('allocated_at')->constrained('users')->nullOnDelete();
            $table->timestamp('picked_at')->nullable()->after('picked_by');
            $table->foreignId('packed_by')->nullable()->after('picked_at')->constrained('users')->nullOnDelete();
            $table->timestamp('packed_at')->nullable()->after('packed_by');
            $table->string('carrier')->nullable()->after('packed_at');
            $table->string('tracking_number')->nullable()->after('carrier');
        });

        Schema::table('goods_issue_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity_allocated')->default(0)->after('quantity');
            $table->unsignedInteger('quantity_picked')->default(0)->after('quantity_allocated');
            $table->unsignedInteger('quantity_packed')->default(0)->after('quantity_picked');
        });

        Schema::create('pick_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_issue_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->nullOnDelete();
            $table->string('batch_number', 50)->nullable();
            $table->string('lot_number', 50)->nullable();
            $table->date('expired_date')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_picked')->default(0);
            $table->string('status', 20)->default('allocated'); // allocated|picked|cancelled
            $table->foreignId('picked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('picked_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['goods_issue_id', 'status']);
            $table->index(['location_id', 'product_id']);
        });

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_number', 40)->unique();
            $table->foreignId('goods_issue_id')->constrained()->cascadeOnDelete();
            $table->string('weight')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pick_line_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('pick_lines');

        Schema::table('goods_issue_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_allocated', 'quantity_picked', 'quantity_packed']);
        });

        Schema::table('goods_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('allocated_by');
            $table->dropConstrainedForeignId('picked_by');
            $table->dropConstrainedForeignId('packed_by');
            $table->dropColumn([
                'strategy', 'allocated_at', 'picked_at', 'packed_at',
                'carrier', 'tracking_number',
            ]);
        });

        DB::statement("ALTER TABLE goods_issues MODIFY COLUMN status ENUM('pending','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
