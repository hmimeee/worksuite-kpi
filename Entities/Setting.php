<?php

namespace Modules\KPI\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'name',
        'value',
    ];
    
    protected $primaryKey = 'name';
    public $incrementing = false;

    protected $table = 'kpi_settings';
    
    public function user()
    {
        return $this->hasMany(User::class, 'user_id');
    }
    
    public static function value($key, $type = 'string')
    {
        $setting = Setting::where('name', $key)->first()->value ?? null;
        if ($type =='number' && $setting) {
            return (int)  $setting;
        }

        if ($type == 'time' && $setting) {
            return Carbon::createFromFormat('h:i A', $setting);
        }
        return Setting::where('name', $key)->first()->value ?? null;
    }
}
