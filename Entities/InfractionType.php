<?php

namespace Modules\KPI\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\KPI\Entities\Infraction;

class InfractionType extends Model
{
    protected $fillable = [
    	'name',
    	'reduction_points',
    	'details',
    ];

    protected $table = 'kpi_infraction_types';

    public function infractions()
    {
    	return $this->hasMany(Infraction::class);
    }
}
