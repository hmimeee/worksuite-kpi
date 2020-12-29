<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToKpiInfractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_infractions', function (Blueprint $table) {
            $table->dropColumn('reduction_points');
        });

        Schema::table('kpi_infractions', function (Blueprint $table) {
            $table->decimal('addition_points')->nullable()->after('infraction_type');
            $table->decimal('reduction_points')->nullable()->after('addition_points');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('kpi_infractions', 'addition_points')) {
            Schema::table('kpi_infractions', function (Blueprint $table) {
                $table->dropColumn('addition_points');
            });
        }
    }
}
