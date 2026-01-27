<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
            if (!Schema::hasColumn('fakulteti', 'uputstvo_preview')) {
                $table->string('uputstvo_preview')->nullable()->after('uputstvo_file');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
            if (Schema::hasColumn('fakulteti', 'uputstvo_preview')) {
                $table->dropColumn('uputstvo_preview');
            }
        });
    }
};
