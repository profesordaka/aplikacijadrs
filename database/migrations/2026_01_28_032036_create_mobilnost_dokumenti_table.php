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
        Schema::create('mobilnost_dokumenti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobilnost_id')->constrained('mobilnosti')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->string('type')->default('other'); // 'learning_agreement', 'transcript', 'other'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobilnost_dokumenti');
    }
};
