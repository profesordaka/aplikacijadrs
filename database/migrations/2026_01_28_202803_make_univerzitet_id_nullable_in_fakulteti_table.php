<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
           
            $table->dropForeign(['univerzitet_id']);

           
            $table->unsignedBigInteger('univerzitet_id')->nullable()->change();

            // vrati foreign key, ali nullable
            $table->foreign('univerzitet_id')
                  ->references('id')
                  ->on('univerziteti')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fakulteti', function (Blueprint $table) {
            $table->dropForeign(['univerzitet_id']);
            $table->unsignedBigInteger('univerzitet_id')->nullable(false)->change();
            $table->foreign('univerzitet_id')
                  ->references('id')
                  ->on('univerziteti')
                  ->cascadeOnDelete();
        });
    }
};
