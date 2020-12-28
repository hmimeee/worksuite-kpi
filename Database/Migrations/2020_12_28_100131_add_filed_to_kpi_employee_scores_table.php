<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFiledToKpiEmployeeScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_employee_scores', function (Blueprint $table) {
            $table->longText('faults')->nullable()->after('out_of');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('kpi_employee_scores', 'faults')) {
            Schema::table('kpi_employee_scores', function (Blueprint $table) {
                $table->dropColumn('faults');
            });
        }
    }
}
