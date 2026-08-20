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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained();
            $table->foreignId('book_id')
                ->constrained()
                ->onDelete('cascade');

            $table->tinyInteger('rating')->unsigned();

            $table->text('comment')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE reviews ADD CONSTRAINT rating_check CHECK (rating BETWEEN 1 AND 5)');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
