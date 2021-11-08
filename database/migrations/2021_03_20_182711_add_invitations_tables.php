<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvitationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("happenings", function($table){
            $table->id();
            $table->string('uuid', 32)->unique();
            $table->string('source_identifier', 1024)->nullable();
            $table->boolean('public')->default(false);
            $table->boolean('managed_happening')->default(true); // If not managed it's a happening that is simply scraped from the internet or public knowledge
            $table->integer('user_id')->default(0); // If not managed and systematically created there is no user
            $table->string('happening_type', 128)->nullable();
            $table->string('happening_name', 256);
            $table->text('happening_information')->nullable();
            $table->string('happening_name_local', 128);
            $table->text('happening_information_local')->nullable();
            $table->string('locality', 128)->default("fi");
            $table->datetime('happening_starts')->nullable();
            $table->datetime('happening_ends')->nullable();
            $table->string('street_address', 256)->nullable();
            $table->string('zipcode', 128)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
            $table->boolean('allow_image_upload')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("invites", function($table){
            $table->id();
            $table->string('uuid', 32)->unique();
            $table->integer('happening_id');
            $table->integer('user_id')->nullable();
            $table->string('invitation_name', 256);
            $table->string('invitee_email', 256)->nullable();
            $table->integer('max_attendees')->default(1);
            $table->integer('confirmed_attendees')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create("invite_user", function(Blueprint $table){
            $table->id();
            $table->string('uuid', 32)->unique();
            $table->integer("invite_id");
            $table->integer("user_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
