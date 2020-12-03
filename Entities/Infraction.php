<?php

namespace Modules\KPI\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Modules\KPI\Entities\InfractionType;

class Infraction extends Model
{
    protected $fillable = [
    	'user_id',
        'infraction_type_id',
    	'infraction_type',
    	'reduction_points',
    	'details',
    ];

    public $dates = ['created_at', 'updated_at'];

    protected $table = 'kpi_infractions';

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function type()
    {
    	return $this->belongsTo(InfractionType::class, 'infraction_type_id');
    }
}
