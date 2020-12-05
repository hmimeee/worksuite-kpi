<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_tracked_data', function (Blueprint $table) {
            $table->string('email');
            $table->date('date');
            $table->timestamp('start');
            $table->timestamp('break_start');
            $table->timestamp('break_end');
            $table->timestamp('end');
            $table->integer('minutes');
            $table->string('leave')->nullable();
            $table->timestamps();

            $table->foreign('email')->references('email')->on('users')->onDelete('cascade');

            $table->primary(['email', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kpi_tracked_data');
    }
}
