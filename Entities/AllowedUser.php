<?php

namespace Modules\KPI\Entities;

use Modules\KPI\Entities\Employee;
use Illuminate\Database\Eloquent\Model;

class AllowedUser extends Model
{
    protected $fillable = [
        'user_id'
    ];
    protected $table = 'kpi_allowed_users';
    protected $primaryKey = 'user_id';
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(Employee::class, 'user_id');
    }
}
