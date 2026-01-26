<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
            if (!Schema::hasColumn('fakulteti', 'uputstvo_file')) {
                $table->string('uputstvo_file')->nullable()->after('uputstvo_za_ocjene');
            }
            if (!Schema::hasColumn('fakulteti', 'image_url')) {
                $table->string('image_url')->nullable()->after('uputstvo_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
            $table->dropColumn('uputstvo_file');
            $table->dropColumn('image_url');
        });
    }
};
