<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('from_warehouse_id')->nullable();
            $table->unsignedBigInteger('to_warehouse_id')->nullable();
            $table->string('batch_number')->nullable();
            $table->integer('quantity');
            $table->enum('type', ['receipt', 'issue', 'transfer', 'adjustment', 'stockopname']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
