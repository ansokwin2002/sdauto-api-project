<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add column only if not exists
        if (!Schema::hasColumn('sliders', 'ordering')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->unsignedInteger('ordering')->default(0)->after('image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sliders', 'ordering')) {
            Schema::table('sliders', function (Blueprint $table) {
                $table->dropColumn('ordering');
            });
        }
    }
};
