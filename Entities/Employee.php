<?php

namespace Modules\KPI\Entities;

use App\Task;
use App\User;
use Carbon\Carbon;

class Employee extends User
{
    protected $table = 'users';

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    

    public function scopeExceptWriters($query)
    {
        return $query->whereHas('roles', function($q){
            return $q->where('name', '<>', 'remote_writer');
        });
    }

    public function workPerformance()
    {
        return $this->hasOne(TaskPerformance::class, 'user_id');
    }

    public function getCompletedTasks()
    {
        $date = request()->month ? Carbon::createFromDate(date('Y'), request()->month, 1) : Carbon::now();

        return $this->belongsToMany(Task::class, 'task_users', 'user_id')->whereBetween('completed_on', [$date->firstOfMonth()->format('Y-m-d H:i:s'), $date->endOfMonth()->format('Y-m-d H:i:s')]);
    }


    public static function taskRating($id)
    {
        $employee = Employee::find($id);
        $tasks = $employee->getCompletedTasks->where('rating', '<>', null);
        $rating = $tasks->count() ? $tasks->sum('rating') / $tasks->count() : 0;
        $html = '';
        foreach (range(1, 5) as $i) {
            $html .= '<span class="fa-stack" style="width:1em"><i class="fa fa-star fa-stack-1x"></i>';
            if ($rating > 0) {
                if ($rating > 0.5) {
                    $html .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
                } else {
                    $html .= '<i class="fa fa-star-half fa-stack-1x text-warning" style="margin-left: -3px;"></i>';
                }
            }
            $rating--;
            $html .= '</span>';
        }
        $html .= ' (' . number_format($tasks->count() ? $tasks->sum('rating') / $tasks->count() : 0, 1) . ')';

        return $html;
    }

    public static function taskScores($id)
    {
        $employee = Employee::find($id);
        $tasks = $employee->getCompletedTasks;

        if ($tasks->count() > 0) {
            $faults = 0;
            $deduct = 20 / $tasks->count();
            foreach ($tasks as $item) {
                if ($item->due_date->format('dmY') < $item->completed_on->format('dmY')) {
                    $faults += 1/$item->users->count();
                }
            }
            $score = number_format(20 - ($deduct * $faults), 2);
        } else {
            $score = number_format(20, 2);
        }

        return $score;
    }
}
