<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SIPIL-inspired (concept only): location barcode + human display label.
 * Not a copy of SIPIL INVN_PROD_LOCATION — keeps our zone/rack/bin tree.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('locations')) {
            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            if (! Schema::hasColumn('locations', 'barcode')) {
                $table->string('barcode', 80)->nullable()->after('code');
            }
            if (! Schema::hasColumn('locations', 'display_name')) {
                $table->string('display_name', 120)->nullable()->after('name');
            }
        });

        // Unique barcode when present (MySQL allows multiple NULLs)
        $idx = DB::select("SHOW INDEX FROM `locations` WHERE Key_name = 'locations_barcode_unique'");
        if (count($idx) === 0) {
            Schema::table('locations', function (Blueprint $table) {
                $table->unique('barcode');
            });
        }

        // Backfill barcode from code for existing rows
        $rows = DB::table('locations')->where(function ($q) {
            $q->whereNull('barcode')->orWhere('barcode', '');
        })->get(['id', 'code', 'name']);
        foreach ($rows as $row) {
            $code = strtoupper(trim((string) $row->code));
            $barcode = str_starts_with($code, 'LOC-') ? $code : 'LOC-'.$code;
            DB::table('locations')->where('id', $row->id)->update([
                'barcode' => $barcode,
                'display_name' => $row->name,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('locations')) {
            return;
        }

        $idx = DB::select("SHOW INDEX FROM `locations` WHERE Key_name = 'locations_barcode_unique'");
        if (count($idx) > 0) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropUnique('locations_barcode_unique');
            });
        }

        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'barcode')) {
                $table->dropColumn('barcode');
            }
            if (Schema::hasColumn('locations', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }
};
