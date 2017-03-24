<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCharacterProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('character_profiles', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('character_id')->unsigned()->index();
			$table->text('body')->nullable();
			$table->timestamps();

            $table->foreign('character_id')
                  ->references('id')->on('characters')
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
        Schema::dropIfExists('character_profiles');
    }
}
