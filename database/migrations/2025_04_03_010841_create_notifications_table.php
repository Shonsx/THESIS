<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())')); // Auto-generate UUID
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->string('type')->nullable();
            $table->string('message')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->foreign('notifiable_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
