<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expired_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->timestamps();
            
            $table->unique(['product_id', 'warehouse_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
