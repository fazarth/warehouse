<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, appearance, notification, etc
            $table->timestamps();
        });

        // Insert default settings
        DB::table('app_settings')->insert([
            ['key' => 'app_name', 'value' => 'Koperasi Digital', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_tagline', 'value' => 'Sistem Manajemen Koperasi Terpadu', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'value' => null, 'type' => 'string', 'group' => 'appearance', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#10b981', 'type' => 'string', 'group' => 'appearance', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'interest_rate_default', 'value' => '12', 'type' => 'integer', 'group' => 'loan', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_loan_amount', 'value' => '50000000', 'type' => 'integer', 'group' => 'loan', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'simpanan_pokok', 'value' => '100000', 'type' => 'integer', 'group' => 'saving', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'simpanan_wajib', 'value' => '50000', 'type' => 'integer', 'group' => 'saving', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
