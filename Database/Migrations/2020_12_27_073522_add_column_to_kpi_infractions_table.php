<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToKpiInfractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_infractions', function (Blueprint $table) {
            $table->unsignedInteger('task_id')->nullable()->after('user_id');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('kpi_infractions', 'task_id')) {
            Schema::table('kpi_infractions', function (Blueprint $table) {
                $table->dropColumn('task_id');
            });
        }
    }
}
