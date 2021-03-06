<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 128);
            $table->string('tag_locality', 2)->default("fi");
            $table->string('tag_name', 256);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('happening_tag', function (Blueprint $table) {
            $table->id();
            $table->integer('happening_id');
            $table->integer('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('happening_tag');
    }
}
