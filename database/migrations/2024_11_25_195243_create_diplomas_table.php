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
        Schema::create('diplomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_type');
            $table->string('size');
            $table->string('diploma_style');
            $table->string('degree_type');
            $table->string('major');
            $table->string('concentration')->nullable();
            $table->string('university_name');
            $table->string('university_city');
            $table->string('university_state');
            $table->string('student_name');
            $table->string('student_city');
            $table->date('graduation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diplomas');
    }
};
