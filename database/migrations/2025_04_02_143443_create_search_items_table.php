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
        Schema::create('search_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('topic')->nullable()->index();
            $table->text('url');
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->boolean('has_image')->default(false)->index();
            $table->string('video')->nullable();
            $table->boolean('has_video')->default(false)->index();
            $table->boolean('unsafe')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_items');
    }
};
