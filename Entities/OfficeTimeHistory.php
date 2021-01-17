<?php

namespace Modules\KPI\Entities;

use Illuminate\Database\Eloquent\Model;

class OfficeTimeHistory extends Model
{
    protected $fillable = [
        'start_time',
        'end_time'
    ];
    protected $table = 'kpi_office_time_histories';
}
