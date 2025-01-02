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
        Schema::table('courses', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable();
            $table->string('who_has_access')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_discounted')->default(false);
            $table->string('discount_code')->nullable();
            $table->integer('discount_percentage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }
};
