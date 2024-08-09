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
        Schema::create('requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('office_id')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('category_id')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('subcategory_id')->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignUlid('requestor_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->smallInteger('priority')->nullable();
            $table->smallInteger('difficulty')->nullable();
            $table->date('target_date')->nullable();
            $table->time('target_time')->nullable();
            $table->datetime('availability_from')->nullable();
            $table->datetime('availability_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
