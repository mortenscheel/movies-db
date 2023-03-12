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
            $table->foreignId('user_id');
            $table->string('title')->fulltext();
            $table->text('tagline')->nullable()->fulltext();
            $table->text('description')->fulltext();
            $table->string('poster')->nullable();
            $table->unsignedInteger('budget');
            $table->unsignedInteger('revenue')->index();
            $table->decimal('runtime')->index();
            $table->decimal('popularity')->index();
            $table->decimal('vote_average');
            $table->unsignedInteger('vote_count');
            $table->char('imdb_id', 9);
            $table->string('homepage')->nullable();
            $table->date('release_date')->nullable()->index();
            $table->timestamps();
        });
        Schema::create('genres', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->timestamps();
        });
        Schema::create('genre_movie', function (Blueprint $table) {
            $table->foreignId('genre_id')->index();
            $table->foreignId('movie_id')->index();
            $table->primary(['genre_id', 'movie_id']);
        });
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->timestamps();
        });
        Schema::create('company_movie', function (Blueprint $table) {
            $table->foreignId('company_id')->index();
            $table->foreignId('movie_id')->index();
            $table->primary(['company_id', 'movie_id']);
        });
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('name')->fulltext();
            $table->timestamps();
        });
        Schema::create('keyword_movie', function (Blueprint $table) {
            $table->foreignId('keyword_id')->index();
            $table->foreignId('movie_id')->index();
            $table->primary(['keyword_id', 'movie_id']);
        });
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('profile')->nullable();
            $table->timestamps();
        });
        Schema::create('cast', function (Blueprint $table) {
            $table->foreignId('movie_id')->index();
            $table->foreignId('person_id')->index();
            $table->unsignedInteger('cast_id')->nullable();
            $table->text('character');
            $table->smallInteger('order')->nullable()->index();
            $table->primary(['movie_id', 'person_id']);
        });
        Schema::create('crew', function (Blueprint $table) {
            $table->foreignId('movie_id')->index();
            $table->foreignId('person_id')->index();
            $table->string('job');
            $table->primary(['movie_id', 'person_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_person');
        Schema::dropIfExists('people');
        Schema::dropIfExists('keyword_movie');
        Schema::dropIfExists('keywords');
        Schema::dropIfExists('company_movie');
        Schema::dropIfExists('companies');
        Schema::dropIfExists('genre_movie');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('movies');
    }
};
