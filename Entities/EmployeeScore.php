<?php

namespace Modules\KPI\Entities;

use Illuminate\Database\Eloquent\Model;

class EmployeeScore extends Model
{
    protected $table = 'kpi_employee_scores';
    protected $fillable = [
        'user_id',
        'attendance_score',
        'work_score',
        'infraction_score',
        'total_score',
        'rating',
        'out_of',
        'time_logged'
    ];

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
}