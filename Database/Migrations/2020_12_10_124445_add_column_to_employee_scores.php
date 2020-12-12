<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToEmployeeScores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_employee_scores', function (Blueprint $table) {
            $table->bigInteger('time_logged')->nullable()->after('attendance_score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('kpi_employee_scores', 'time_logged')) {
            Schema::table('kpi_employee_scores', function (Blueprint $table) {
                $table->dropColumn('time_logged');
            });
        }
    }
}
