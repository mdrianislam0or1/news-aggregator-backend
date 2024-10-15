<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('url')->unique();
            $table->string('source');
            $table->string('category')->nullable();
            $table->string('image_url', 255)->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
