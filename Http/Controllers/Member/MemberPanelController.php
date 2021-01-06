<?php

namespace Modules\KPI\Http\Controllers\Member;

use App\Leave;
use ZipArchive;
use App\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Froiden\Envato\Helpers\Reply;
use Modules\KPI\Entities\Setting;
use Modules\KPI\Entities\Employee;
use Modules\KPI\Entities\AllowedUser;
use Modules\KPI\Entities\TrackedData;
use Illuminate\Support\Facades\Storage;
use Modules\KPI\Datatables\RatingsDataTable;
use Modules\KPI\Datatables\InfractionsDataTable;
use App\Http\Controllers\Member\MemberBaseController;

class MemberPanelController extends MemberBaseController
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:employee']);
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $this->pageTitle = 'KPI Overview';
        $this->topScorers = Employee::topFiveScorers();
        $this->employees = Employee::exceptWriters()->active()->get()->sortByDesc('scores.total_score');
        $this->settings = Setting::all()->pluck('value', 'name');

        return view('kpi::member.index', $this->data);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function infractions(InfractionsDataTable $dataTable)
    {
        $this->pageTitle = 'Infractions';
        $this->employees = Employee::exceptWriters()->active()->get();
        $this->settings = Setting::all()->pluck('value', 'name');
        if (!auth()->user()->hasKPIAccess) {
            $this->employees = Employee::where('id', auth()->id())->get();
        }

        return $dataTable->render('kpi::member.infractions', $this->data);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function rating(Request $request)
    {
        $employee = Employee::find($request->employee ?? auth()->id());
        $tasks = [];
        if ($employee) {
            $allTasks = $employee->finishedCreatedTasks->merge($employee->getCompletedTasks);
            foreach ($allTasks as $task) {
                $rate = $task->rating;
                $rating = '';
                foreach (range(1, 5) as $i) {
                    $rating .= '<span class="fa-stack" style="width:1em"><i class="fa fa-star fa-stack-1x"></i>';
                    if ($rate > 0) {
                        $rating .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
                    }
                    $rate--;
                    $rating .= '</span>';
                }

                $members = '';
                foreach ($task->users as $member) {
                    $members .= '<a href="' . route('member.employees.show', [$member->id]) . '">';
                    $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($member->name) . '" title="' . ucwords($member->name) . '" src="' . $member->image_url . '"
                alt="user" class="img-circle" width="25" height="25"> ';
                    $members .= '</a>';
                }

                $heading = "<label class='badge badge-success'>Task:</label> <a href='javascript:;' onclick='showTask($task->id)'>$task->heading</a>";

                $faults = Employee::taskScores($employee->id, 'array');
                if (count($faults) > 0 && array_key_exists($task->id, $faults['task_faults'])) {
                    $heading .= " <label class='label label-danger'>" . $faults['task_faults'][$task->id]['reason'] . "</label>";
                }

                $tasks[] = [
                    'id' => $task->id,
                    'heading' => $heading,
                    'rating' => $rating,
                    'assignee' => $members
                ];
            }

            $allArticles = $employee->completedArticles->merge($employee->completedCreatedArticles);
            foreach ($allArticles as $article) {

                $rate = $article->rating;
                $rating = '';
                foreach (range(1, 5) as $i) {
                    $rating .= '<span class="fa-stack" style="width:1em"><i class="fa fa-star fa-stack-1x"></i>';
                    if ($rate > 0) {
                        $rating .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
                    }
                    $rate--;
                    $rating .= '</span>';
                }

                $members = '';
                $members .= '<a href="' . route('member.employees.show', [$article->assignee]) . '">';
                $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($article->getAssignee->name) . '" title="' . ucwords($article->getAssignee->name) . '" src="' . $article->getAssignee->image_url . '"
                alt="user" class="img-circle" width="25" height="25"> ';
                $members .= '</a>';

                $heading = '<label class="badge badge-info">Article:</label> <a href="javascript:;" onclick="showTask(' . $article->id . ', \'article\')">' . $article->title . '</a>';

                $faults = Employee::taskScores($employee->id, 'array');
                if (count($faults) > 0 && array_key_exists($article->id, $faults['article_faults'])) {
                    $heading .= " <label class='label label-danger'>" . $faults['article_faults'][$article->id]['reason'] . "</label>";
                }

                $tasks[] = [
                    'id' => $article->id,
                    'heading' => $heading,
                    'rating' => $rating,
                    'assignee' => $members
                ];
            }
        }

        if ($request->ajax()) {
            return response()->json($tasks);
        }

        $this->pageTitle = 'Task Ratings & Ontime Scores';
        $this->employees = Employee::exceptWriters()->active()->get();
        $this->settings = Setting::all()->pluck('value', 'name');
        $this->tasks = $tasks;

        if (!auth()->user()->hasKPIAccess) {
            $this->employees = Employee::where('id', auth()->id())->get();
        }

        return view('kpi::member.rating', $this->data);
    }


    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function attendances(Request $request)
    {
        $this->pageTitle = 'Attendances';
        $this->employees = Employee::exceptWriters()->active()->get();
        $this->logData = Employee::trackedData();
        $this->settings = Setting::all()->pluck('value', 'name');
        
        if (!auth()->user()->hasKPIAccess) {
            $this->employees = Employee::where('id', auth()->id())->get();
        }

        return view('kpi::member.attendances', $this->data);
    }


    public function userData(Employee $user)
    {
        $reasons = Employee::attendanceScore($user->id, 'array');

        //Collect the dates of the month
        $year = request()->year ?? date('Y');
        $month = request()->month ?? date('m');
        $date = Carbon::createFromDate($year, $month, 01);
        $startDate = $date->firstOfMonth()->format('Y-m-d');
        $endDate = $date->endOfMonth()->format('Ymd') <= now()->format('Ymd') ? $date->endOfMonth()->format('Y-m-d') : now()->format('Y-m-d');
        $dates = CarbonPeriod::create($startDate, $endDate);

        foreach ($dates as $dt) {
            $udata = TrackedData::where('email', $user->email)->where('date', $dt->format('Y-m-d'))->first();
            $isHoliday = Holiday::where('date', $dt->format('Y-m-d'))->first();
            $hasLeave = Leave::where('user_id', $user->id)->where('leave_date', $dt->format('Y-m-d'))->where('duration', '<>', 'half day')->first();
            $hasHalfLeave = Leave::where('user_id', $user->id)->where('leave_date', $dt->format('Y-m-d'))->where('duration', 'half day')->first();
            $has_reason = null;
            if ($udata && array_key_exists($udata->date->format('Y-m-d'), $reasons)) {
                $reason = $reasons[$udata->date->format('Y-m-d')];
                $has_reason = $reason == 'Half Day Leave' ? '<label class="label label-inverse">' . $reason . '</label>' : '<label class="label label-danger">' . $reason . '</label>';
            }

            if ($udata) {
                $bindData[] = [
                    'date' => $dt->format('d-m-Y') . ' ' . $has_reason,
                    'start' => $udata->start->format('h:i a'),
                    'break_start' => $udata->break_start->format('h:i a'),
                    'break_end' => $udata->break_end->format('h:i a'),
                    'end' => $udata->end->format('h:i a'),
                    'minutes' => $udata->minutes,
                    'leave' => $udata->leave,
                ];
            } elseif (!$udata && !$isHoliday && !$hasLeave && !$hasHalfLeave) {
                $bindData[] = [
                    'date' => $dt->format('d-m-Y') . ' <label class="label label-danger">Absence</label>',
                    'start' => '--:--',
                    'break_start' => '--:--',
                    'break_end' => '--:--',
                    'end' => '--:--',
                    'minutes' => '--:--',
                    'leave' => '--:--',
                ];
            } elseif (!$udata && !$isHoliday && ($hasLeave || $hasHalfLeave)) {
                $bindData[] = [
                    'date' => $dt->format('d-m-Y') . ' <label class="label label-inverse">' . ($hasHalfLeave ? 'Half Day Leave' : 'Full Day Leave') . '</label>',
                    'start' => '--:--',
                    'break_start' => '--:--',
                    'break_end' => '--:--',
                    'end' => '--:--',
                    'minutes' => '--:--',
                    'leave' => '--:--',
                ];
            } elseif (!$udata && $isHoliday && !$hasLeave && !$hasHalfLeave) {
                $bindData[] = [
                    'date' => $dt->format('d-m-Y') . ' <label class="label label-success">Holiday</label>',
                    'start' => '--:--',
                    'break_start' => '--:--',
                    'break_end' => '--:--',
                    'end' => '--:--',
                    'minutes' => '--:--',
                    'leave' => '--:--',
                ];
            }
        }

        return $bindData ?? [];
    }

    /**
     * Show the page for an employee.
     * @return Response
     */
    public function profile(Employee $user)
    {
        $this->employee = $user;
        $this->settings = Setting::all()->pluck('value', 'name');
        $this->employees = Employee::exceptWriters()->active()->get()->sortByDesc('scores.total_score');

        $rate = $user->scores->rating;
        $rating = '';
        foreach (range(1, 5) as $i) {
            $rating .= '<span class="fa-stack" style="width:1em"><i class="fa fa-star fa-stack-1x"></i>';
            if ($rate > 0) {
                $rating .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
            }
            $rate--;
            $rating .= '</span>';
        }

        $this->rating = $rating;

        if ($user->scores->total_score > 100) {
            $this->performance = 'best';
        } elseif ($user->scores->total_score >= 80) {
            $this->performance = 'good';
        } elseif ($user->scores->total_score >= 40) {
            $this->performance = 'medium';
        } else {
            $this->performance = 'bad';
        }

        return view('kpi::profile', $this->data);
    }

    /**
     * View KPI documentation
     * @return \Illuminate\View\View
     */
    public function doc()
    {
        $this->pageTitle = 'KPI Documentation';
        $this->settings = Setting::all()->pluck('value', 'name');

        return view('kpi::member.doc', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('kpi::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('kpi::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('kpi::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
