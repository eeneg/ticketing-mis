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
        Schema::create('offices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('acronym')->unique()->nullable();
            $table->string('address')->nullable();
            $table->string('room')->nullable();
            $table->string('logo')->nullable();
            $table->string('building')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->ulid('office_id')->nullable()->constrained()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');

        Schema::table('users', function (Blueprint $table) {
            $table->ulid('office_id')->nullable()->change();
        });
    }
};
