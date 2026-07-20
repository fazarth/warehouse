<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
            ['key' => 'app_name', 'value' => 'Warehouse Management', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_tagline', 'value' => 'Sistem Manajemen Gudang', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'app_logo', 'value' => null, 'type' => 'string', 'group' => 'appearance', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'primary_color', 'value' => '#14B8A6', 'type' => 'string', 'group' => 'appearance', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_name', 'value' => 'Demo Warehouse', 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_address', 'value' => null, 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'company_phone', 'value' => null, 'type' => 'string', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer', 'group' => 'inventory', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
