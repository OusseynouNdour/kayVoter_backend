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
        Schema::create('candidats', function (Blueprint $table) {
            $table->id();
            $table->foreignid('etudiant_id')->constrained('etudiants')->onDelete('cascade');
            $table->foreignid('ufr_id')->constrained('ufrs')->onDelete('cascade');
            $table->foreignid('elections_id')->constrained('elections')->onDelete('cascade');
            $table->string('nom');
            $table->string('prenom');
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidats');
    }
};
