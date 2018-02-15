<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticeClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_claims', function (Blueprint $table) {
            $table->increments('id');
            $table->char('user_id', 36)->index();
            $table->integer('character_id')->unsigned()->index();
            $table->integer('world_id')->unsigned()->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

            $table->foreign('character_id')
                  ->references('id')->on('characters')
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
        Schema::dropIfExists('notice_claims');
    }
}
