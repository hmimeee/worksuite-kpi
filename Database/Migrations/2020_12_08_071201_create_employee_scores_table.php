<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_employee_scores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->unique();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('attendance_score', 10, 1)->default(0);
            $table->decimal('work_score', 10, 1)->default(0);
            $table->decimal('infraction_score', 10, 1)->default(0);
            $table->decimal('total_score', 10, 1)->default(0);
            $table->decimal('rating', 10, 1)->default(0);
            $table->decimal('out_of', 10, 1)->default(0);
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
        Schema::dropIfExists('kpi_employee_scores');
    }
}
