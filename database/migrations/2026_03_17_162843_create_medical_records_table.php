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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->integer('patient_id')->unsigned()->unique();
            $table->string('blood_type', 3)->nullable();
            $table->string('allergies', 255)->nullable();
            $table->string('cronic_diseases', 255)->nullable();
            $table->string('medications', 255)->nullable();
            $table->string('family_history', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
