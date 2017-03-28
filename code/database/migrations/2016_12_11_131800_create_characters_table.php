<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharactersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->char('user_id', 36)->index();
            $table->integer('world_id')->unsigned();
            $table->boolean('verified');
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('race')->nullable();
            $table->string('clan')->nullable();
            $table->string('nameday')->nullable();
            $table->string('guardian')->nullable();
            $table->string('city_state')->nullable();
            $table->string('active_class')->nullable();
            $table->integer('status');
            $table->timestamps();

            $table->primary('id');

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('world_id')
                  ->references('id')->on('worlds')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characters');
    }
}
