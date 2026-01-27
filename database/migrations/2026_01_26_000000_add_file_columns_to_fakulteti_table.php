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
            // Dodaj novu kolonu za preview PDF-a
            if (!Schema::hasColumn('fakulteti', 'uputstvo_preview')) {
                $table->string('uputstvo_preview')->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
            if (Schema::hasColumn('fakulteti', 'uputstvo_file')) {
                $table->dropColumn('uputstvo_file');
            }
            if (Schema::hasColumn('fakulteti', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('fakulteti', 'uputstvo_preview')) {
                $table->dropColumn('uputstvo_preview');
            }
        });
    }
};
