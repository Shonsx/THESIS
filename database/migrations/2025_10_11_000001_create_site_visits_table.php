<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visited_at'); // the day of visit
            $table->unsignedBigInteger('user_id')->nullable(); // registered user id, if logged in
            $table->string('ip_address')->nullable(); // IP for guests
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen')->nullable(); // for online status window
            $table->timestamps();

            $table->index(['visited_at']);
            $table->index(['user_id']);
            $table->index(['ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};