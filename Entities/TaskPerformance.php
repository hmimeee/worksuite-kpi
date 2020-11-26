<?php

namespace Modules\KPI\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TaskPerformance extends Model
{
    protected $fillable = [
        'user_id',
        'rating',
        'score',
    ];

    protected $table = 'kpi_task_performances';

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
}
