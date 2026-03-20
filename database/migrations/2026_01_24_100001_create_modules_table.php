<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('📦');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_core')->default(false); // Core modules cannot be disabled
            $table->string('package')->default('basic'); // basic, pro, full
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
