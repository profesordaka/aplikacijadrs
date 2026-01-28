<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mobilnosti', function (Blueprint $table) {
            $table->string('tip_mobilnosti')->nullable()->after('fakultet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobilnosti', function (Blueprint $table) {
            $table->dropColumn('tip_mobilnosti');
        });
    }
};
