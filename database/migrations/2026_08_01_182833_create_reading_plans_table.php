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
        Schema::create('reading_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                ->constrained()->onDelete('restrict');
            $table->foreignId('user_id')
                ->constrained()->onDelete('cascade');
            $table->date('target_date');
            $table->enum('status', ['in_progress', 'completed', 'overdue'])->default('in_progress');
            $table->date('started_date')->nullable();
            $table->date('finished_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_plans');
    }
};
