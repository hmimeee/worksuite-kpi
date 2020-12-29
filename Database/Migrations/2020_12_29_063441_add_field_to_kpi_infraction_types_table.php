<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToKpiInfractionTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_infraction_types', function (Blueprint $table) {
            $table->dropColumn('reduction_points');
        });

        Schema::table('kpi_infraction_types', function (Blueprint $table) {
            $table->decimal('addition_points')->nullable()->after('details');
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
        if (Schema::hasColumn('kpi_infraction_types', 'addition_points')) {
            Schema::table('kpi_infraction_types', function (Blueprint $table) {
                $table->dropColumn('addition_points');
            });
        }
    }
}
