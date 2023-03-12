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
        Schema::table('movies', function (Blueprint $table) {
            $table->fullText('title');
            $table->fullText('tagline');
            $table->fullText('description');
        });
        Schema::table('keywords', function (Blueprint $table) {
            $table->fullText(['name']);
        });
    }
};
