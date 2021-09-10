<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHappeningPhotos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('happening_photos', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 16);
            $table->integer('happening_id');
            $table->string('original_name', 1024);
            $table->string('filename', 1024);
            $table->string('mimetype', 1024);
            $table->integer('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('happening_photos');
    }
}
