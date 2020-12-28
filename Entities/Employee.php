<?php

namespace Modules\KPI\Entities;

use App\Task;
use App\User;
use DateTime;
use App\Leave;
use DatePeriod;
use App\Holiday;
use DateInterval;
use Carbon\Carbon;
use Modules\KPI\Entities\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Article\Entities\Article;
use Modules\KPI\Entities\AllowedUser;
use Modules\Article\Entities\ArticleActivityLog;

class Employee extends User
{
    protected $table = 'users';

    public function loggedData()
    {
        return $this->hasMany(TrackedData::class, 'email', 'email');
    }

    public function scores()
    {
        $update = $this->hasOne(EmployeeScore::class, 'user_id')->first()->updated_at ?? null;
        if (!$update || $update->diffInHours(now()) > 2) {
            Employee::updateScore();
        }

        return $this->hasOne(EmployeeScore::class, 'user_id');
    }

    public function completedArticles()
    {
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');

        $date = Carbon::createFromDate($year, $month, date('d'));
        if ($date->format('Ym') < 202012) {
            $date = now();
        }
        $startDate = $date->firstOfMonth()->format('Y-m-d H:i:s');
        $endDate = $date->endOfMonth()->format('Y-m-d H:i:s');

        $artIds = ArticleActivityLog::where('label', 'article_writing_status')
        ->where('details', 'submitted the article for approval.')
        ->whereBetween('created_at', [$startDate, $endDate])
        // ->where('user_id', $this->id)
        ->pluck('article_id');

        return $this->hasMany(Article::class, 'assignee')->whereIn('id', $artIds);
    }

    public function completedCreatedArticles()
    {
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');

        $date = Carbon::createFromDate($year, $month, date('d'));
        if ($date->format('Ym') < 202012) {
            $date = now();
        }
        $startDate = $date->firstOfMonth()->format('Y-m-d H:i:s');
        $endDate = $date->endOfMonth()->format('Y-m-d H:i:s');

        $artIds = ArticleActivityLog::where('label', 'article_writing_status')
        ->where('details', 'approved the article and transferred for publishing.')
        ->whereBetween('created_at', [$startDate, $endDate])
        // ->where('user_id', $this->id)
        ->pluck('article_id');

        return $this->hasMany(Article::class, 'creator')->whereIn('id', $artIds);
    }
    
    public function leaves()
    {
        return $this->hasMany(Leave::class, 'user_id')->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('login', 'enable');
    }

    public function scopeExceptWriters($query)
    {
        $expIDs = User::whereHas('roles', function ($q) {
            return $q->where('name', 'remote_writer')->orWhere('name', 'client');
        })->pluck('id')->toArray();

        //Remove unwanted users from list (Using ID)
        $exceptUsers = Setting::value('except_users', 'array') ?? [];
        if (count($exceptUsers))
        $expIDs = array_merge($expIDs, $exceptUsers);

        return $query->whereNotIn('id', $expIDs);
    }

    public function getCompletedTasks()
    {
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');

        $date = Carbon::createFromDate($year, $month, date('d'));
        if ($date->format('Ym') < 202012) {
            $date = now();
        }
        $startDate = $date->firstOfMonth()->format('Y-m-d H:i:s');
        $endDate = $date->endOfMonth()->format('Y-m-d H:i:s');

        return $this->belongsToMany(Task::class, 'task_users', 'user_id')->whereBetween('completed_on', [$startDate, $endDate])->where('board_column_id', 2);
    }

    public function finishedCreatedTasks()
    {
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');

        $date = Carbon::createFromDate($year, $month, date('d'));
        if ($date->format('Ym') < 202012) {
            $date = now();
        }
        $startDate = $date->firstOfMonth()->format('Y-m-d H:i:s');
        $endDate = $date->endOfMonth()->format('Y-m-d H:i:s');
        
        return $this->hasMany(Task::class, 'created_by')->whereBetween('completed_on', [$startDate, $endDate])->where('board_column_id', 2);
    }

    public function infractions()
    {
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');

        $date = Carbon::createFromDate($year, $month, date('d'));
        if ($date->format('Ym') < 202012) {
            $date = now();
        }
        $startDate = $date->firstOfMonth()->format('Y-m-d H:i:s');
        $endDate = $date->endOfMonth()->format('Y-m-d H:i:s');

        return $this->hasMany(Infraction::class, 'user_id')->whereBetween('created_at', [$startDate, $endDate]);
    }

    public static function infractionScore($id)
    {
        $infractions = Employee::find($id)->infractions;
        $baseScore = Setting::value('infraction_score', 'number') ?? 0;
        $score = $baseScore - $infractions->sum('reduction_points');
        if ($infractions->count() <= 0) {
             $score = $baseScore;
        }

        $attendance = Employee::userTrackedData($id);
        if ($attendance->count() == 0) {
            $score = 0;
        }
        return number_format($score,1);
    }


    public static function taskRating($id, $json = null)
    {
        $employee = Employee::find($id);
        $tasks = $employee->getCompletedTasks->where('rating', '<>', null);
        $articles =  $employee->completedArticles->where('rating', '<>', null);
        $allRating = $articles->sum('rating') + $tasks->sum('rating');
        $count = $tasks->count() + $articles->count();
        $avgRating = $count ? $allRating / $count : 0;
        $rating = $avgRating;
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
        $rating = number_format($avgRating, 1);
        $html .= ' (' . $rating . ')';

        if ($json) {
            return $rating;
        }

        return $html;
    }

    private static function filterPoint($days = 0, $points = 0, $deduct = true)
    {
        $d = $points;

        if ($deduct) {
            if ($days == 2) {
                $d = $points * 1.2;
            }
            if ($days == 3) {
                $d = $points * 1.4;
            }
            if ($days == 4) {
                $d = $points * 1.6;
            }
            if ($days == 5) {
                $d = $points * 1.8;
            }
            if ($days == 6) {
                $d = $points * 2.2;
            }
            if ($days > 6) {
                $d = $points * 2.5;
            }
        } else {
            if ($days == 1) {
                $d = $points / 5;
            }
            if ($days == 2) {
                $d = $points / 4;
            }
            if ($days == 3) {
                $d = $points / 3;
            }
            if ($days == 4) {
                $d = $points / 2.5;
            }
            if ($days > 4) {
                $d = $points / 2;
            }
        }

        return $d;
    }

    public static function taskScores($id, $json = null)
    {
        $employee = Employee::find($id);
        $employeeScore = EmployeeScore::where('user_id', $id)->first();
        if ($employeeScore->updated_at->diffInHours() < 3 && $json != 'array') {
            return $employeeScore->work_score;
        }
        if ($employeeScore->faults != null && $employeeScore->updated_at->diffInHours() < 3 && $json == 'array') {
            return $employeeScore->faults;
        }
        
        $tasks = $employee->getCompletedTasks;
        $articles =  $employee->completedArticles;
        $createdArticles =  $employee->completedCreatedArticles;
        $createdTasks = $employee->finishedCreatedTasks;
        $baseScore = Setting::value('work_score', 'number') ?? 0;
        $score = number_format($baseScore, 1);
        $totalWorks = $tasks->count() + $createdTasks->where('created_by', '<>', $id)->count() + $articles->count() + $createdArticles->where('creator', '<>', $id)->count();
        $deduct = $totalWorks ? ($baseScore /  $totalWorks) : 0;

        //Check task where the user is assignee
        if ($tasks->count() > 0) {
            // $faults = 0;
            $d = 0;
            $a = 0;
            foreach ($tasks as $item) {
                //Collect the dates of the week
                $period = new DatePeriod(
                    new DateTime($item->due_date->format('Y-m-d')),
                    new DateInterval('P1D'),
                    new DateTime($item->due_date->addDays(6)->format('Y-m-d'))
                );

                //Check if the user has taken any leaves
                $leaves = $employee->leaves->where('duration', '<>', 'half day');
                foreach ($period as $pdate) {
                    $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                    if (!$dleave) {
                        $setDate = $pdate;
                        break;
                    }
                }
                
                //Check if the task was not completed in time
                if ($setDate->format('Ymd') < $item->completed_on->format('Ymd')) {
                    // $faults += 1 / $item->users->count();
                    $days = $item->completed_on->diffInDays($setDate);
                    // if ($days > 15) {
                    //     $infraction = Infraction::where('task_id', $item->id)->first();
                    //     if (!$infraction) {
                    //         Infraction::create([
                    //             'created_by' => 1,
                    //             'user_id' => $id,
                    //             'task_id' => $item->id,
                    //             'infraction_type' => 'Delayed to complete task',
                    //             'details' => 'Delayed too long time to complete the task. <br/>
                    //         Task ID: ' . $item->id . '<br/>
                    //         Task Heading: ' . $item->heading . '<br/>
                    //         Task Due: ' . $item->due_date->format('d-m-Y').
                    //         'Task Link: <a href="' .route('member.all-tasks.index').'?view-task='. $item->id . '">'. $item->heading.'</a><br/>',
                    //             'reduction_points' => 5
                    //         ]);
                    //     }
                    // } else {
                        $filterPoints = Employee::filterPoint($days, $deduct) / $item->users->count();
                        $d = $d + $filterPoints;

                        $taskFaults[$item->id] = [
                            'task_id' => $item->id,
                            'task_heading' => $item->heading,
                            'deduct' => $filterPoints,
                            'reason' => 'Delayed to complete',
                            'days' => $days,
                        ];
                    // }
                }

                //Check if the task completed before the deadline
                if ($item->due_date->format('Ymd') > $item->completed_on->format('Ymd')) {
                    if (!$item->users->where('id', $item->created_by)->first()) {
                        $days = $item->due_date->diffInDays($item->completed_on);
                        $filterPoints = Employee::filterPoint($days, $deduct, false) / $item->users->count();
                        $a = $a + $filterPoints;
                        $taskFaults[$item->id] = [
                            'task_id' => $item->id,
                            'task_heading' => $item->heading,
                            'add' => $filterPoints,
                            'reason' => 'Completed early',
                            'days' => $days,
                        ];
                    }
                }
            }

            //Deduct/Add points with the score
            $score = ($score - $d) + $a;
        }

        //Check the articles where the user is assignee
        if ($articles->count() > 0) {
            $d = 0;
            $a = 0;
            // $faults = 0;
            foreach ($articles as $art) {
                //Get the due date
                $due = Carbon::createFromFormat('Y-m-d', $art->writing_deadline);
                //Get the completed date
                $completed = $art->logs->where('details', 'submitted the article for approval.')->last()->created_at ?? now();

                //Collect the dates of the week
                $period = new DatePeriod(
                    new DateTime($due->format('Y-m-d')),
                    new DateInterval('P1D'),
                    new DateTime($due->addDays(6)->format('Y-m-d'))
                );

                //Check if the user has taken any leaves
                $leaves = $employee->leaves->where('duration', '<>', 'half day');
                foreach ($period as $pdate) {
                    $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                    if (!$dleave) {
                        $setDate = $pdate;
                        break;
                    }
                }

                if ($completed == null || $setDate->format('Ymd') < $completed->format('Ymd')) {
                    // $faults += 1;
                    if ($completed == null) {
                        $days = now()->diffInDays($setDate);
                    } else {
                        $days = $completed->diffInDays($setDate);
                    }

                    $filterPoints = Employee::filterPoint($days, $deduct);
                    $d = $d + $filterPoints;

                    $articleFaults[$art->id] = [
                        'article_id' => $art->id,
                        'article_title' => $art->title,
                        'deduct' => $filterPoints,
                        'reason' => 'Delayed to complete',
                        'days' => $days,
                    ];
                }

                if ($completed != null && $due->format('Ymd') > $completed->format('Ymd')) {
                    $days = $due->diffInDays($completed);
                    $filterPoints = Employee::filterPoint($days, $deduct, false);
                    $a = $a + $filterPoints;

                    $articleFaults[$art->id] = [
                        'article_id' => $art->id,
                        'article_title' => $art->title,
                        'add' => $filterPoints,
                        'reason' => 'Completed early',
                        'days' => $days,
                    ];
                }
            }
            
            $score = ($score - $d) + $a;
        }

        //Check the task where the user is creator
        if ($createdTasks->count() > 0) {
            // $faults = 0;
            $d = 0;

            foreach ($createdTasks as $task) {

                //Collect the dates of the week
                $period = new DatePeriod(
                    new DateTime($task->completed_on->format('Y-m-d')),
                    new DateInterval('P1D'),
                    new DateTime($task->completed_on->addDays(6)->format('Y-m-d'))
                );

                //Check if the user has taken any leaves
                $leaves = $employee->leaves->where('duration', '<>', 'half day');
                foreach ($period as $pdate) {
                    $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                    if (!$dleave) {
                        $setDate = $pdate;
                        break;
                    }
                }

                //Check if the user has approved the task in time
                if (($task->isApproved == null && now()->format('Ymd') > $setDate->format('Ymd')) || ($task->isApproved && $task->isApproved->created_at->format('Ymd') > $setDate->format('Ymd'))) {
                    // $faults += 1;
                    if ($task->isApproved == null) {
                        $days = now()->diffInDays($setDate) - 2;
                    } else {
                        $days = $task->isApproved->created_at->diffInDays($setDate) - 2;
                    }

                    if ($days > 0) {
                        $filterPoints = Employee::filterPoint($days, $deduct);
                        $d = $d + $filterPoints;

                        $taskFaults[$task->id] = [
                            'task_id' => $task->id,
                            'task_title' => $task->heading,
                            'deduct' => $filterPoints,
                            'reason' => 'Delayed to approve',
                        ];
                    }
                }
            }

            $score = $score - $d;
        }

        //Check if the user delayed to approve the articles
        if ($createdArticles->count() > 0) {
            // $faults = 0;
            $d = 0;
            foreach ($createdArticles as $arti) {
                $completed = $arti->logs->where('details', 'submitted the article for approval.')->last()->created_at ?? null;
                $approved = $arti->logs->where('details', 'approved the article and transferred for publishing.')->first()->created_at ?? now();
                if ($completed) {
                    //Collect the dates of the week
                    $period = new DatePeriod(
                        new DateTime($completed->format('Y-m-d')),
                        new DateInterval('P1D'),
                        new DateTime($completed->addDays(6)->format('Y-m-d'))
                    );

                    //Check if the user has taken any leaves
                    $leaves = $employee->leaves->where('duration', '<>', 'half day');
                    foreach ($period as $pdate) {
                        $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                        if (!$dleave) {
                            $setDate = $pdate;
                            break;
                        }
                    }
                }

                //Check if the user approved the article in time
                if ($completed != null && $approved->format('Ymd') > $setDate->format('Ymd')) {
                    // $faults += 1;
                    $days = $approved->diffInDays($setDate) - 2;
                    if ($days > 0) {
                        $filterPoints = Employee::filterPoint($days, $deduct);
                        $d = $d + $filterPoints;

                        $articleFaults[$arti->id] = [
                            'article_id' => $arti->id,
                            'article_title' => $arti->title,
                            'deduct' => $filterPoints,
                            'reason' => 'Delayed to approve',
                        ];
                    }
                }
            }
            
            $score = $score - $d;
        }

        if ($json == 'array') {
            $allFaults = array('task_faults' => $taskFaults ?? [], 'article_faults' => $articleFaults ?? []);
            $storedFaults = $employeeScore->update([
                'work_score' => $score,
                'faults' => $allFaults
            ]);
            return $employeeScore->faults;
        }

        if ($totalWorks == 0) {
            $score = 0;
        }

        $score = number_format($score, 1);

        return $score;
    }


    public static function allScores($id)
    {
        $tscore = Employee::taskScores($id, 'json');
        $iscore = Employee::infractionScore($id, 'json');
        $ascore = Employee::attendanceScore($id);
        $total = $tscore + $iscore + $ascore;
        $outof = Setting::value('infraction_score') + Setting::value('work_score') + Setting::value('attendance_score') ?? 0;

        return (object) ['total' => $total, 'outof' => $outof];
    }

    public static function topFiveScorers()
    {
        //Remove unwanted users from list (Using ID)
        $exceptUsers = Setting::value('except_users', 'array') ?? [];
        $expIDs = 1;
        if (count($exceptUsers) > 0)
            $expIDs = $exceptUsers;

        $topFive['total'] = EmployeeScore::orderBy('total_score', 'desc')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['work'] = EmployeeScore::orderBy('work_score', 'desc')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['infraction'] = EmployeeScore::orderBy('infraction_score', 'desc')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['attendance'] = EmployeeScore::orderBy('attendance_score', 'desc')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['total_low'] = EmployeeScore::orderBy('total_score')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['work_low'] = EmployeeScore::orderBy('work_score')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['infraction_low'] = EmployeeScore::orderBy('infraction_score')->whereNotIn('user_id', $expIDs)->get()->take(5);
        $topFive['attendance_low'] = EmployeeScore::orderBy('attendance_score')->whereNotIn('user_id', $expIDs)->get()->take(5);

        return $topFive;
    }

    public static function trackedData()
    {
        $dbData = TrackedData::where('date', '>=', date('Y-m-d'))->get()->last();
        $sdate = request()->year ?? date('Y');
        $ldate = request()->month ?? date('m');
        $date = Carbon::createFromDate($sdate, $ldate, 01);
        $startDate = $date->firstOfMonth()->format('Y-m-d');
        $endDate = $date->endOfMonth()->format('Y-m-d');
        $getMail = Setting::value('tracker_mail');
        $getKey = Setting::value('tracker_key');

        if ($dbData == null || $dbData->updated_at->diffInHours(now()) > 2) {
            $response = Http::withBasicAuth($getMail, $getKey)
            ->get('https://www.webwork-tracker.com/rest-api/reports/daily-timeline', [
                'start_date' => now()->firstOfMonth()->format('Y-m-d'),
                'end_date' => now()->endOfMonth()->format('Y-m-d')
            ]);

            $data = $response->json();
            foreach ($data['dateReport'] as $key => $dateReport) {
                $email = $dateReport['email'];
                $date = $dateReport['dateTracked'];
                $tasks = $dateReport['projects'][0]['tasks'];

                foreach ($tasks as $key => $task) {
                    $entries = $task['timeEntries'];
                    foreach ($entries as $key => $entry) {
                        $start = $date . ' ' . $entry['beginDatetime'];
                        $end = $date . ' ' . $entry['endDatetime'];

                        $bindData[$email][$date][] = [
                            'start' => $start,
                            'end' => $end,
                            'minutes' => $entry['minutes'],
                        ];
                    }
                }
            }

            foreach ($bindData as $email => $data) {
                foreach ($data as $date => $info) {
                    $fromTime = Setting::value('start_break_time', 'time')->format('Hi');
                    $allowedTime = Setting::value('allowed_time');
                    $breakEnd = [];

                    //Searching time from break time
                    foreach ($info as $key => $value) {
                        // $time = Carbon::createFromFormat('Y-m-d H:i', $value['start'], 'UTC')->setTimezone('Asia/Dhaka')->format('Hi');
                        // $eTime = Carbon::createFromFormat('Y-m-d H:i', $value['start'], 'UTC')->setTimezone('Asia/Dhaka')->format('Y-m-d H:i');
                        // $sTime = Carbon::createFromFormat('Y-m-d H:i', $value['end'], 'UTC')->setTimezone('Asia/Dhaka')->format('Y-m-d H:i');
                        $time = Carbon::createFromFormat('Y-m-d H:i', $value['start'])->format('Hi');
                        $eTime = Carbon::createFromFormat('Y-m-d H:i', $value['start'])->format('Y-m-d H:i');
                        $sTime = Carbon::createFromFormat('Y-m-d H:i', $value['end'])->format('Y-m-d H:i');
                        if ($time >= $fromTime + $allowedTime) {
                            $breakEnd[] = $eTime;
                        }

                        if ($time < $fromTime + $allowedTime) {
                            $breakStart[] = $sTime;
                        }
                    }
                    $start = Carbon::createFromFormat('Y-m-d H:i', $info[0]['start']);
                    $end = Carbon::createFromFormat('Y-m-d H:i', end($info)['end']);
                    $minutes = array_sum(array_column($info, 'minutes'));

                    $employee = Employee::where('email', $email)->first();
                    if ($employee != null) {
                        $hasLeave = Leave::where('user_id', $employee->id)->where('leave_date',  $date)->first();

                        if ($hasLeave == null || $hasLeave->duration == 'half day') {
                            $bindData[] = TrackedData::updateOrCreate(['date' => $date, 'email' => $email], [
                                'start' => $start->format('Y-m-d H:i'),
                                'break_start' => !empty($breakStart) ? end($breakStart) : $start->format('Y-m-d H:i'), //From the searching time from break time foreach
                                'break_end' => !empty($breakEnd) ? $breakEnd[0] : $end->format('Y-m-d H:i'), //From the searching time from break time foreach
                                'end' => $end->format('Y-m-d H:i'),
                                'minutes' => $start->diffInMinutes($end),
                                'leave' => $hasLeave ? ($hasLeave->duration == 'half day' ? 'half day' : null) : null,
                            ]);
                        }
                    }
                }
            }
        }
        
        $getData = TrackedData::whereBetween('date', [$startDate, $endDate])->get();

        return $getData;
    }


    public static function userTrackedData($id, $type = 'array')
    {
        $employee = Employee::find($id);
        $userData = Employee::trackedData()->where('email', $employee->email);

        if (request()->array && $type != 'object') {
            $userData = $userData->toArray();
            foreach ($userData as $key => $data) {
                $date = Carbon::create($data['date'])->format('Y-m-d');
                $isHoliday = Holiday::where('date', $date)->first();

                if ($isHoliday == null) {
                    $fetchData[] = [
                        'date' => $data['leave'] != null ? $date.' <span class="label label-danger">Half Day Leave</span>' : $date,
                        'start' => Carbon::create($data['start'])->format('h:i a'),
                        'break_start' => Carbon::create($data['break_start'])->format('h:i a'),
                        'break_end' => Carbon::create($data['break_end'])->format('h:i a'),
                        'end' => Carbon::create($data['end'])->format('h:i a'),
                    ];
                }
            }
            return $fetchData ?? [];
        }

        return $userData;
    }

    public static function attendanceScore($id, $return = 'score')
    {
        $trackedData = Employee::userTrackedData($id, 'object');
        $faults = 0;
        $baseScore = Setting::value('attendance_score', 'number') ?? 0;
        $attendances = $trackedData->count();
        $deduct = $attendances ? $baseScore / $attendances : 0;
        $allowedTime = Setting::value('allowed_time');
        //Hour * 60 + Minutes = Total Minutes
        $startTime = Setting::value('start_time', 'time')->format('H') * 60 + Setting::value('start_time', 'time')->format('i') ?? 670;
        $endTime = Setting::value('end_time', 'time')->format('H') * 60 + Setting::value('end_time', 'time')->format('i') ?? 1139;
        $breakEnd = Setting::value('end_break_time', 'time')->format('H') * 60 + Setting::value('end_break_time', 'time')->format('i') ?? 905;
        $remainingTime = 0;
        
        foreach ($trackedData as $data) {
            $start = $data->start->format('H:i');
            $breakBegin = $data->break_start->format('H:i');
            $breakFinish = $data->break_end->format('H:i');
            $end = $data->end->format('H:i');
            $date = $data->date->format('Y-m-d');

            $start = explode(':', $start);
            $start = ($start[0]*60)+$start[1];
            $breakBegin = explode(':', $breakBegin);
            $breakBegin = ($breakBegin[0] * 60) + $breakBegin[1];
            $breakFinish = explode(':', $breakFinish);
            $breakFinish = ($breakFinish[0] * 60) + $breakFinish[1];
            $end = explode(':', $end);
            $end = ($end[0] * 60) + $end[1];
            $checkedDate = null;
            $hasLeave = Leave::where('user_id', $id)->where('leave_date', $date)->first();

            if ($hasLeave == null) {
                //Check if the user start office after 11:10 am
                if ($start > $startTime) {
                    //Check the delayed time in minutes
                    $late = ($start - $startTime) - $allowedTime;

                    //Check if the user end office before or exact 07:00 pm
                    if ($late > 0 && $end <= $endTime) {
                        $faults += 1;
                        $checkedDate = $date;
                        
                        $faultCount[] = [
                            'date' => $date,
                            'gap' => $late,
                            'reason' => 'Late in'
                        ];
                    }

                    //Check if the user end office after 07:00 pm
                    if ($late > 0 && $checkedDate != $date &&  $end > $endTime && $late > ($end - $endTime)) {
                        $faults += 1;
                        $checkedDate = $date;

                        $faultCount[] = [
                            'date' => $date,
                            'gap' => $late,
                            'reason' => 'Late in'
                        ];
                    }
                }

                //Check if the user end office before 07:00 pm
                if ($checkedDate != $date && $end < $endTime) {
                    //Check the early time in minutes
                    $early = ($end - $endTime) - $allowedTime;

                    //Check if the user start office after 11:10 am
                    if ($early > 0 && $start > $startTime) {
                        $faults += 1;
                        $checkedDate = $date;

                        $faultCount[] = [
                            'date' => $date,
                            'gap' => $early,
                            'reason' => 'Early out'
                        ];
                    }

                    //Check if the user start office before 11:00 am
                    if ($early > 0 && $checkedDate != $date && $start < $startTime && $early > ($startTime - $start)) {
                        $faults += 1;
                        $checkedDate = $date;

                        $faultCount[] = [
                            'date' => $date,
                            'gap' => $early,
                            'reason' => 'Early out'
                        ];
                    }
                }


                //Check if user took more time than break time limit
                // if ($checkedDate != $date && $breakFinish > $breakEnd && ($breakFinish - $breakBegin) > 65) {
                //     $faults += 1;
                // }
            }
        }

        //check if the user has filled the faults times in the week
        if (isset($faultCount)) {
            foreach ($faultCount as $fault) {
                $startOfWeek = Carbon::create($fault['date'])->startOfWeek(); //Starts from sunday
                $endOfWeek = Carbon::create($fault['date'])->endOfWeek();
                $leavesCount = Leave::where('user_id', $id)
                ->where('duration', '<>', 'half day')
                ->whereBetween('leave_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->count();
                $halfLeavesCount = Leave::where('user_id', $id)
                ->where('duration', 'half day')
                ->whereBetween('leave_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->count() / 2;

                $weekTotal = ($endTime - $startTime) * (6 - ($leavesCount + $halfLeavesCount));

                $weekEmpTotal = Employee::userTrackedData($id, 'object')
                ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->sum('minutes');

                if ($weekEmpTotal > $weekTotal) {
                    $extraTimes = $weekEmpTotal - $weekTotal;
                    if (isset($filledGap)) {
                        $extraTimes = $extraTimes - array_sum($filledGap);
                    }
                    $remainingTime = $extraTimes - $fault['gap'];
                    if ($remainingTime >= 0) {
                        $faults = $faults - 1;
                        $filledGap[] = $fault['gap'];
                    }

                    $timeArr[] = $remainingTime;
                } else {
                    $attendFaults[$fault['date']] = $fault['reason'];
                }
            }
        }

        if ($return == 'array') {
            return $attendFaults ?? [];
        }
        
        if ($faults > 0) {
            $score = $baseScore - ($deduct * $faults);
        }

        if (!isset($score)) {
            $score = $baseScore;
        }

        if ($attendances == 0) {
            $score = 0;
        }

        return number_format($score, 1);
    }

    /**
     * Updating all data to the database to reduce server pressure
     * @return true
     */
    public static function updateScore()
    {
        $allEmp = Employee::exceptWriters()->active()->get();
        $outOf = Setting::value('attendance_score') + Setting::value('work_score') + Setting::value('infraction_score');
        foreach ($allEmp as $emp) {
            EmployeeScore::updateOrCreate(['user_id' => $emp->id], [
                'attendance_score' => Employee::attendanceScore($emp->id),
                'work_score' => Employee::taskScores($emp->id, 'json'),
                'infraction_score' => Employee::infractionScore($emp->id),
                'total_score' => Employee::allScores($emp->id, 'json')->total,
                'rating' => Employee::taskRating($emp->id, 'json'),
                'out_of' => $outOf,
                'time_logged' => $emp->loggedData->sum('minutes'),
            ]);
        }

        return true;
    }
}
