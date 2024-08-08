<?php

use App\Models\Request;
use App\Models\User;
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
        Schema::create('actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Request::class);
            $table->foreignIdFor(User::class);
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->datetime('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
