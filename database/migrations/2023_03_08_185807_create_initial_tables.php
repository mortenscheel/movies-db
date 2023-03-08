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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->string('title')->index();
            $table->string('tagline')->nullable();
            $table->text('description');
            $table->string('poster');
            $table->boolean('adult')->default(false);
            $table->unsignedInteger('budget');
            $table->unsignedInteger('revenue');
            $table->decimal('runtime')->index();
            $table->decimal('popularity')->index();
            $table->decimal('vote_average');
            $table->unsignedInteger('vote_count');
            $table->char('imdb_id', 9);
            $table->string('homepage')->nullable();
            $table->date('released_at');
            $table->timestamps();
        });
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('genre_movie', function (Blueprint $table) {
            $table->foreignId('genre_id');
            $table->foreignId('movie_id');
            $table->primary(['genre_id', 'movie_id']);
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('company_movie', function (Blueprint $table) {
            $table->foreignId('company_id');
            $table->foreignId('movie_id');
            $table->primary(['company_id', 'movie_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_movie');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('genre_movie');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('movies');
    }
};
