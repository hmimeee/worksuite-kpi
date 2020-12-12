<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToKpiInfractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('kpi_infractions', function (Blueprint $table) {
        //     $table->unsignedInteger('created_by')->after('id');
        //     $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // if (Schema::hasColumn('kpi_infractions', 'created_by')) {
        //     Schema::table('kpi_infractions', function (Blueprint $table) {
        //         $table->dropColumn('created_by');
        //     });
        // }
    }
}
