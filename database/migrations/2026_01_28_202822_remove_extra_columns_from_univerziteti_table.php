<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('univerziteti', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'drzava', 'grad']);
        });
    }

    public function down(): void
    {
        Schema::table('univerziteti', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('drzava')->nullable();
            $table->string('grad')->nullable();
        });
    }
};
