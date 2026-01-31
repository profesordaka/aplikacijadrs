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
        Schema::create('nastavne_liste', function (Blueprint $table) {
            $table->id();

            $table->foreignId('predmet_id')->constrained('predmeti')->cascadeOnDelete();
            $table->foreignId('fakultet_id')->constrained('fakulteti')->cascadeOnDelete();

            $table->string('studijska_godina', 20);

            // Može biti ili link ili fajl
            $table->string('link')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();

            // Dozvoljavamo više nastavnih lista za isti predmet i fakultet,
            // ali ne duplikat iste godine za isti predmet na istom fakultetu
            $table->unique(['predmet_id', 'fakultet_id', 'studijska_godina'], 'nl_unique_version');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nastavne_liste');
    }
};
