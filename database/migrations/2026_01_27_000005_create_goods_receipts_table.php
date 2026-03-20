<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->date('receipt_date');
            $table->string('document_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'cancelled'])->default('pending');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expired_date')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
    }
};
