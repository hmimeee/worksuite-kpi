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

    public function hasKPIAccess()
    {
        return $this->belongsTo(AllowedUser::class, 'user_id') ? true : false;
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
        })->pluck('id');

        //Remove unwanted users from list (Using ID)
        $expIDs[] = 1;

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
        return $this->hasMany(Task::class, 'created_by')->whereBetween('completed_on', [$date->firstOfMonth()->format('Y-m-d H:i:s'), $date->endOfMonth()->format('Y-m-d H:i:s')])->where('board_column_id', 2);
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

    public static function taskScores($id, $json = null)
    {
        $employee = Employee::find($id);
        $tasks = $employee->getCompletedTasks;
        $articles =  $employee->completedArticles;
        $createdArticles =  $employee->completedCreatedArticles;
        $createdTasks = $employee->finishedCreatedTasks;
        $baseScore = Setting::value('work_score', 'number') ?? 0;
        $score = number_format($baseScore, 1);
        $totalWorks = $tasks->count() + $articles->count();
        $deduct = $totalWorks ? $baseScore /  $totalWorks : 0;

        if ($tasks->count() > 0) {
            $faults = 0;
            foreach ($tasks as $item) {
                $period = new DatePeriod(
                    new DateTime($item->due_date->format('Y-m-d')),
                    new DateInterval('P1D'),
                    new DateTime($item->due_date->addDays(6)->format('Y-m-d'))
                );
                $leaves = $employee->leaves;

                foreach ($period as $pdate) {
                    $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                    if (!$dleave) {
                        $setDate = $pdate;
                        break;
                    }
                }
                
                if ($setDate->format('Ymd') < $item->completed_on->format('Ymd')) {
                    $faults += 1 / $item->users->count();
                }
            }
            $score = $baseScore - ($deduct * $faults);
        }

        if ($articles->count() > 0) {
            $faults = 0;
            foreach ($articles as $art) {
                $due = Carbon::createFromFormat('Y-m-d', $art->writing_deadline);
                $completed = $art->logs->where('details', 'submitted the article for approval.')->last()->created_at ?? null;
                $period = new DatePeriod(
                    new DateTime($due->format('Y-m-d')),
                    new DateInterval('P1D'),
                    new DateTime($due->addDays(6)->format('Y-m-d'))
                );
                $leaves = $employee->leaves;

                foreach ($period as $pdate) {
                    $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                    if (!$dleave) {
                        $setDate = $pdate;
                        break;
                    }
                }

                if ($completed != null && $setDate->format('Ymd') < $completed->format('Ymd')) {
                    $faults += 1;
                }
            }
            
            $score = $baseScore - ($deduct * $faults);
        }

        if ($createdTasks->count() > 0) {
            $faults = 0;
            foreach ($createdTasks as $task) {
                $period = new DatePeriod(
                    new DateTime($task->completed_on->format('Y-m-d')),
                    new DateInterval('P1D'),
                    new DateTime($task->completed_on->addDays(6)->format('Y-m-d'))
                );
                $leaves = $employee->leaves;

                foreach ($period as $pdate) {
                    $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                    if (!$dleave) {
                        $setDate = $pdate;
                        break;
                    }
                }

                if ($task->isApproved && $task->created_by != $employee->id && $task->isApproved->created_at->format('Ymd') - $setDate > 2) {
                    $faults += 1;
                }
            }

            $score = $score - ($deduct * $faults);
        }

        if ($createdArticles->count() > 0) {
            $faults = 0;
            foreach ($createdArticles as $arti) {
                $completed = $arti->logs->where('details', 'submitted the article for approval.')->last()->created_at ?? null;
                $approved = $arti->logs->where('details', 'approved the article and transferred for publishing.')->first()->created_at ?? null;
                if ($completed) {
                    $period = new DatePeriod(
                        new DateTime($completed->format('Y-m-d')),
                        new DateInterval('P1D'),
                        new DateTime($completed->addDays(6)->format('Y-m-d'))
                    );
                    $leaves = $employee->leaves;

                    foreach ($period as $pdate) {
                        $dleave = $leaves->where('leave_date', $pdate->format('Y-m-d'))->first();
                        if (!$dleave) {
                            $setDate = $pdate;
                            break;
                        }
                    }
                }

                if ($completed != null && $approved != null && $arti->creator != $employee->id && $approved->format('Ymd') - $setDate->format('Ymd') > 2) {
                    $faults += 1;
                }
            }

            $score = $baseScore - ($deduct * $faults);
        }

        $allCount = $tasks->count()+ $articles->count()+$createdArticles->count()+$createdTasks->count();
        if ($allCount == 0) {
            $score = 0;
        }

        $score = number_format($score, 1);

        if ($json) {
            return $score;
        }

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
        $allEmp = Employee::exceptWriters()->active()->get();
        foreach ($allEmp as $emp) {
            $scr[$emp->id] = Employee::allScores($emp->id)->total;
            $wscr[$emp->id] = Employee::taskScores($emp->id);
            $iscr[$emp->id] = Employee::infractionScore($emp->id);
            $ascr[$emp->id] = Employee::attendanceScore($emp->id);
        }

        arsort($scr);
        arsort($wscr);
        arsort($iscr);
        arsort($ascr);

        $scr = array_keys(array_slice($scr, 0, 5, true));
        $wscr = array_keys(array_slice($wscr, 0, 5, true));
        $iscr = array_keys(array_slice($iscr, 0, 5, true));
        $ascr = array_keys(array_slice($ascr, 0, 5, true));

        $topFive['total'] = Employee::whereIn('id', $scr)->orderBy(DB::raw("FIELD(id," . join(',', $scr) . ")"))->get();
        $topFive['work'] = Employee::whereIn('id', $wscr)->orderBy(DB::raw("FIELD(id," . join(',', $wscr) . ")"))->get();
        $topFive['infraction'] = Employee::whereIn('id', $iscr)->orderBy(DB::raw("FIELD(id," . join(',', $iscr) . ")"))->get();
        $topFive['attendance'] = Employee::whereIn('id', $ascr)->orderBy(DB::raw("FIELD(id," . join(',', $ascr) . ")"))->get();

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

        if ($dbData == null || $dbData->updated_at->diffInHours(now()) > 2) {
            // $response = Http::withBasicAuth("faisal@viserx.com", "qsu9VC\-`'V")
            $response = Http::withBasicAuth("faisal@viserx.com", "nv3ba7rvo2hlfymx1n4byd")
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
                    $fromTime = 1450;
                    $breakEnd = [];

                    //Searching time from break time
                    foreach ($info as $key => $value) {
                        $time = Carbon::createFromFormat('Y-m-d H:i', $value['start'], 'UTC')->setTimezone('Asia/Dhaka')->format('Hi');
                        $eTime = Carbon::createFromFormat('Y-m-d H:i', $value['start'], 'UTC')->setTimezone('Asia/Dhaka')->format('Y-m-d H:i');
                        $sTime = Carbon::createFromFormat('Y-m-d H:i', $value['end'], 'UTC')->setTimezone('Asia/Dhaka')->format('Y-m-d H:i');
                        if ($time >= $fromTime) {
                            $breakEnd[] = $eTime;
                        }

                        if ($time < $fromTime) {
                            $breakStart[] = $sTime;
                        }
                    }
                    $start = Carbon::createFromFormat('Y-m-d H:i', $info[0]['start'], 'UTC')->setTimezone('Asia/Dhaka');
                    $end = Carbon::createFromFormat('Y-m-d H:i', end($info)['end'], 'UTC')->setTimezone('Asia/Dhaka');
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
                                'minutes' => $minutes,
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


    public static function userTrackedData($id)
    {
        $employee = Employee::find($id);
        $userData = Employee::trackedData()->where('email', $employee->email);

        if (request()->array) {
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

    public static function attendanceScore($id)
    {
        $trackedData = Employee::userTrackedData($id);
        $faults = 0;

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
                //Hour * 60 + Minutes = Total Minutes
                $allowedTime = Setting::value('allowed_time');
                $startTime = Setting::value('start_time', 'time')->format('H') * 60 + Setting::value('start_time', 'time')->format('i') ?? 670;
                $endTime = Setting::value('end_time', 'time')->format('H') * 60 + Setting::value('end_time', 'time')->format('i') ?? 1139;
                $breakEnd = Setting::value('end_break_time', 'time')->format('H') * 60 + Setting::value('end_break_time', 'time')->format('i') ?? 905;

                //Check if the user start office after 11:10 am
                if ($start > $startTime) {
                    //Check the delayed time in minutes
                    $late = ($start - $startTime) - $allowedTime;

                    //Check if the user end office before or exact 07:00 pm
                    if ($end <= $endTime) {
                        $faults += 1;
                        $checkedDate = $date;
                    }

                    //Check if the user end office after 07:00 pm
                    if ($checkedDate != $date &&  $end > $endTime && $late > ($end - $endTime)) {
                        $faults += 1;
                    }
                }

                //Check if the user end office before 07:00 pm
                if ($checkedDate != $date && $end < $endTime) {
                    //Check the early time in minutes
                    $early = ($endTime - $end) - $allowedTime;

                    //Check if the user start office after 11:10 am
                    if ($start > $startTime) {
                        $faults += 1;
                        $checkedDate = $date;
                    }

                    //Check if the user start office before 11:00 am
                    if ($checkedDate != $date && $start < $startTime && $early > ($startTime - $start)) {
                        $faults += 1;
                    }
                }
                
                //Check if user took more time than break time limit
                // if ($checkedDate != $date && $breakFinish > $breakEnd && ($breakFinish - $breakBegin) > 65) {
                //     $faults += 1;
                // }
            }
        }

        $attendances = $trackedData->count();
        $baseScore = Setting::value('attendance_score', 'number') ?? 0;
        if ($faults > 0) {
            $score = $baseScore - ($baseScore / $attendances) * $faults;
        } else {
            $score = $baseScore;
        }

        if ($attendances == 0) {
            $score = 0;
        }

        return number_format($score, 1);
    }
}
