<?php

namespace Modules\KPI\Http\Controllers\Member;

use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Froiden\Envato\Helpers\Reply;
use Modules\KPI\Entities\Setting;
use Modules\KPI\Entities\Employee;
use Illuminate\Support\Facades\Storage;
use Modules\KPI\Datatables\RatingsDataTable;
use Modules\KPI\Datatables\InfractionsDataTable;
use App\Http\Controllers\Member\MemberBaseController;
use Modules\KPI\Entities\AllowedUser;

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
        $this->employees = Employee::exceptWriters()->active()->get();
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
        if (!$user->hasKPIAccess) {
            $userData = Employee::userTrackedData(auth()->id());
        }
        $userData = Employee::userTrackedData($user->id);

        return $userData;
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
