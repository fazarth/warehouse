<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_number')->unique();
            $table->string('destination');
            $table->date('issue_date');
            $table->string('delivery_note')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('shipped_by')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
            
            $table->foreign('shipped_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('goods_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_issue_items');
        Schema::dropIfExists('goods_issues');
    }
};
