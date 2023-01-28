<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransitionLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transition_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('performer');
            $table->morphs('transitionable');
            $table->string('blueprint');
            $table->string('source')->nullable();
            $table->string('target');
            $table->text('context')->nullable();
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
        Schema::dropIfExists('transition_history');
    }
}
