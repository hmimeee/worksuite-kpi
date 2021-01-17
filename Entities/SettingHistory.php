<?php

namespace Modules\KPI\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SettingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'details',
    ];
    protected $table = 'kpi_setting_histories';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
