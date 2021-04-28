<?php

namespace Modules\KPI\Http\Controllers\Admin;

use DateTime;
use App\Leave;
use DatePeriod;
use ZipArchive;
use App\Holiday;
use DateInterval;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Froiden\Envato\Helpers\Reply;
use Modules\KPI\Entities\Setting;
use Modules\KPI\Entities\Employee;
use Illuminate\Support\Facades\Http;
use Modules\KPI\Entities\AllowedUser;
use Modules\KPI\Entities\TrackedData;
use Illuminate\Support\Facades\Storage;
use Modules\KPI\Entities\SettingHistory;
use Yajra\DataTables\Facades\DataTables;
use Modules\KPI\Entities\OfficeTimeHistory;
use Modules\KPI\Datatables\RatingsDataTable;
use Modules\KPI\Datatables\InfractionsDataTable;
use App\Http\Controllers\Admin\AdminBaseController;

class AdminPanelController extends AdminBaseController
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
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
        
        return view('kpi::admin.index', $this->data);
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
        
        return $dataTable->render('kpi::admin.infractions', $this->data);
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
                    $members .= '<a href="' . route('admin.employees.show', [$member->id]) . '">';
                    $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($member->name) . '" title="' . ucwords($member->name) . '" src="' . $member->image_url . '"
                alt="user" class="img-circle" width="25" height="25"> ';
                    $members .= '</a>';
                }

                $heading = "<label class='badge badge-success'>Task:</label> <a href='javascript:;' onclick='showTask($task->id)'>$task->heading</a>";
                
                $faults = Employee::taskScores($employee->id, 'array');
                if (count($faults) > 0 && array_key_exists($task->id, $faults['task_faults'])) {
                     $heading .= " <label class='label label-danger'>".$faults['task_faults'][$task->id]['reason']."</label>";
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
                $members .= '<a href="' . route('admin.employees.show', [$article->assignee]) . '">';
                $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($article->getAssignee->name) . '" title="' . ucwords($article->getAssignee->name) . '" src="' . $article->getAssignee->image_url . '"
                alt="user" class="img-circle" width="25" height="25"> ';
                $members .= '</a>';

                $heading = '<label class="badge badge-info">Article:</label> <a href="javascript:;" onclick="showTask('.$article->id.', \'article\')">'.$article->title.'</a>';

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
        
        return view('kpi::admin.rating', $this->data);
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
        
        return view('kpi::admin.attendances', $this->data);
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

            if ($udata && !$isHoliday) {
                $bindData[] = [
                    'date' => $dt->format('d-m-Y') . ' ' . $has_reason,
                    'start' => $udata->start->format('h:i a'),
                    'break_start' => $udata->break_start->format('h:i a'),
                    'break_end' => $udata->break_end->format('h:i a'),
                    'end' => $udata->end->format('h:i a'),
                    'minutes' => $udata->minutes,
                    'leave' => $udata->leave,
                ];
            } elseif ($udata && $isHoliday) {
                $bindData[] = [
                    'date' => $dt->format('d-m-Y') . ' <label class="label label-success">Holiday</label>',
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

    public function settings(Request $request)
    {
        if ($request->has('update_setting')) {
            $settings = $request->only([
                'start_time',
                'end_time',
                'start_break_time',
                'end_break_time',
                'attendance_score',
                'work_score',
                'infraction_score',
                'allowed_time',
                'except_users',
                'tracker_mail',
                'tracker_key'
            ]);

            //Check if updated office start time
            if ($request->start_time != Setting::find('start_time')->value) {
                $startTime = Setting::find('start_time');
                $addToHistory = OfficeTimeHistory::create([
                    'start_time' => $startTime->value,
                    'end_time' => $request->end_time
                ]);

                $this->history('office_time', 'updated office start time from ' . $startTime->value . ' to ' . $request->start_time);
            }

            //Check if updated office end time
            if ($request->end_time != Setting::find('end_time')->value) {
                $endTime = Setting::find('end_time');
                $addToHistory = OfficeTimeHistory::create([
                    'start_time' => $request->start_time,
                    'end_time' => $endTime->value
                ]);
                
                $this->history('office_time', 'updated office end time from ' . $endTime->value . ' to ' . $request->end_time);
            }

            //Except users ids array to string
            $settings['except_users'] = implode(',', $request->except_users);

            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(['name' => $key], ['value' => $value]);
            }

            return back()->withSuccess('Settings updated successfully');
        }

        if ($request->has('update_module') && $request->hasFile('module')) {
            $file = $request->file('module');
            $filename = $file->getClientOriginalName();
            $uploaded = $file->store('temp');

            //Check if the module is a KPI module
            if (substr($filename, 0, 5) != 'KPI_v') {
                Storage::delete($uploaded);
                return Reply::error('Package is not a KPI module!');
            }

            //Extract if the module is verified
            $zip = new ZipArchive;
            $res = $zip->open(public_path('/user-uploads/'.$uploaded));
            if ($res === TRUE) {
                $zip->extractTo(base_path('Modules'));
                $zip->close();
                Storage::delete($uploaded);

                return Reply::success('Successfully updated!');
            } else {
                Storage::delete($uploaded);
                return Reply::error('Something went wrong!');
            }
        }

        //upload kpi documentation
        if ($request->hasFile('documentation') && $request->has('upload_documentation')) {
            $request->validate(['documentation' => 'mimes:pdf']);
            $file = $request->file('documentation');
            $filename = $file->getClientOriginalName();
            $uploaded = $file->storeAs('kpi', 'kpidoc.pdf');
            Setting::updateOrCreate(['name' => 'kpidoc'], ['value' => $uploaded]);
            return Reply::success('Uploaded documentation successfully');
        }

        if ($request->has('add_allowed_users') && $request->allowed_users != null && $request->allowed_users != '') {
            foreach ($request->allowed_users as $userId) {
                $addedUsers[] = AllowedUser::updateOrCreate([
                    'user_id' => $userId
                ]);
            }

            return isset($addedUsers) ? Reply::success('Added successfully') : Reply::error('Something went wrong');
        }

        if ($request->has('remove_permission')) {
            AllowedUser::find($request->allowed_user)->delete();
            return Reply::success('Removed successfully');
        }

        if ($request->has('update_scores')) {
            if (!$request->date) {
                return Reply::error('Please select the date first!');
            }
            $date = Carbon::create($request->date . '-01');
            $request['year'] = $date->format('Y');
            $request['month'] = $date->format('m');
            Employee::updateScore(true);
            
            return Reply::success('Scores updated successfully');
        }

        if ($request->has('update_attendance_data')) {
            if (!$request->date) {
                return Reply::error('Please select the date first!');
            }
            $date = Carbon::create($request->date.'-01');
            $request['year'] = $date->format('Y');
            $request['month'] = $date->format('m');
            Employee::trackedData();

            return Reply::success('Data updated successfully');
        }

        $this->pageTitle = 'KPI: Settings';
        $this->settings = Setting::all()->pluck('value', 'name');
        $this->allowedUsers = AllowedUser::all();
        $this->history = SettingHistory::all();
        $this->employees = Employee::exceptWriters()->active()->whereNotIn('id', $this->allowedUsers->pluck('user_id'))->get();
        $this->allEmployees = Employee::active()->get();

        return view('kpi::admin.settings', $this->data);
    }

    /**
     * Show the page for an employee.
     * @return Response
     */
    public function profile(Employee $user)
    {
        $this->employee = $user;
        $this->settings = Setting::all()->pluck('value', 'name');
        $this->employees = auth()->user()->hasKPIAccess ? Employee::exceptWriters()->active()->get()->sortByDesc('scores.total_score') : Employee::whereId(auth()->id())->get();

        $rate = $user->scores->first()->rating;
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
        if ($user->scores->first()->total_score > 100) {
            $this->performance = 'best';
        } elseif ($user->scores->first()->total_score >= 80) {
            $this->performance = 'good';
        } elseif ($user->scores->first()->total_score >= 40) {
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

        return view('kpi::admin.doc', $this->data);
    }

    /**
     * Setting office time changing history
     */
    public function history($key, $details)
    {
        SettingHistory::create([
            'user_id' => auth()->id(),
            'key' => $key,
            'details' => $details
        ]);
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
