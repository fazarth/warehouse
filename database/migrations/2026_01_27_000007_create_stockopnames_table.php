<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stockopnames', function (Blueprint $table) {
            $table->id();
            $table->string('stockopname_number')->unique();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->date('stockopname_date');
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('stockopname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stockopname_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->integer('system_qty')->default(0);
            $table->integer('physical_qty')->default(0);
            $table->integer('difference')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stockopname_items');
        Schema::dropIfExists('stockopnames');
    }
};
