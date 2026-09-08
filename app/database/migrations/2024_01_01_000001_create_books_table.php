<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('publisher')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('language', 10)->default('en');
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->timestamps();

            $table->index('title');
            $table->index('author');
            $table->index(['title', 'author']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
