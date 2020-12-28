<?php

namespace Modules\KPI\Entities;

use App\Task;
use Illuminate\Database\Eloquent\Model;
use App\User;
use Modules\KPI\Entities\InfractionType;

class Infraction extends Model
{
    protected $fillable = [
        'created_by',
        'user_id',
        'task_id',
        'infraction_type_id',
    	'infraction_type',
    	'reduction_points',
    	'details',
    ];

    public $dates = ['created_at', 'updated_at'];

    protected $table = 'kpi_infractions';

    public function creator()
    {
        return $this->belongsTo(User::class, 'creted_by');
    }

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function type()
    {
    	return $this->belongsTo(InfractionType::class, 'infraction_type_id');
    }
}
