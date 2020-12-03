<?php

namespace Modules\KPI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TrackedData extends Model
{
    protected $table = 'kpi_tracked_data';
    protected $primaryKey = ['email', 'date'];
    public $incrementing = false;

    protected $fillable = [
        'email',
        'date',
        'start',
        'break_start',
        'break_end',
        'end',
        'minutes',
        'leave',
    ];

    public $dates = [
        'date',
        'start',
        'end',
        'break_start',
        'break_end',
        'created_at',
        'updated_at',
    ];

    /**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    public function user()
    {
        return $this->hasMany(Employee::class, 'email', 'email');
    }
}
