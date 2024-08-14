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
        Schema::create('assignees', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Request::class);
            $table->foreignIdFor(User::class);
            $table->ulid('assigner_id');
            $table->string('response')->default('pending')->nullable();
            $table->datetime('responded_at')->nullable();
            $table->timestamps();
            $table->unique(['request_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignees');
    }
};
