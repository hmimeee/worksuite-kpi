<?php

namespace Modules\KPI\Http\Controllers\Admin;

use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Froiden\Envato\Helpers\Reply;
use Modules\KPI\Entities\Setting;
use Modules\KPI\Entities\Employee;
use Illuminate\Support\Facades\Storage;
use Modules\KPI\Datatables\RatingsDataTable;
use Modules\KPI\Datatables\InfractionsDataTable;
use App\Http\Controllers\Admin\AdminBaseController;
use Modules\KPI\Entities\AllowedUser;

class AdminPanelController extends AdminBaseController
{
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
                        if ($rate > 0.5) {
                            $rating .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
                        } else {
                            $rating .= '<i class="fa fa-star-half fa-stack-1x text-warning" style="margin-left: -3px;"></i>';
                        }
                    }
                    $rate--;
                    $rating .= '</span>';
                }

                $members = '';
                foreach ($task->users as $member) {
                    $members .= '<a href="' . route('admin.employees.show', [$member->id]) . '">';
                    $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($member->name) . '" src="' . $member->image_url . '"
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


            foreach ($employee->completedArticles as $article) {

                $rate = $article->rating;
                $rating = '';
                foreach (range(1, 5) as $i) {
                    $rating .= '<span class="fa-stack" style="width:1em"><i class="fa fa-star fa-stack-1x"></i>';
                    if ($rate > 0) {
                        if ($rate > 0.5) {
                            $rating .= '<i class="fa fa-star fa-stack-1x text-warning"></i>';
                        } else {
                            $rating .= '<i class="fa fa-star-half fa-stack-1x text-warning" style="margin-left: -3px;"></i>';
                        }
                    }
                    $rate--;
                    $rating .= '</span>';
                }

                $members = '';
                $members .= '<a href="' . route('admin.employees.show', [$article->assignee]) . '">';
                $members .= '<img data-toggle="tooltip" data-original-title="' . ucwords($article->getAssignee->name) . '" src="' . $article->getAssignee->image_url . '"
                alt="user" class="img-circle" width="25" height="25"> ';
                $members .= '</a>';

                $heading = "<label class='badge badge-info'>Article:</label> <a href='javascript:;' onclick='showTask($article->id, article)'>$article->title</a>";

                $tasks[] = [
                    'id' => $article->id,
                    'heading' => $heading,
                    'rating' => $rating,
                    'assignee' => $members
                ];
            }
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
        $userData = Employee::userTrackedData($user->id);

        return $userData;
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
                'allowed_time'
            ]);

            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(['name' => $key], ['value' => $value]);
            }

            return back()->withSuccess('Settings updated successfully');
        }

        if ($request->has('update_module') && $request->hasFile('module')) {
            $file = $request->file('module');
            $filename = $file->getClientOriginalName();
            $uploaded = $file->store('temp');

            if (substr($filename, 0, 5) != 'KPI_v') {
                Storage::delete($uploaded);
                return Reply::error('Package is not a KPI module!');
            }

            $zip = new ZipArchive;
            $res = $zip->open(public_path('/user-uploads/'.$uploaded));
            if ($res === TRUE) {
                $zip->extractTo(base_path('Modules\\'));
                $zip->close();
                Storage::delete($uploaded);

                return Reply::success('Successfully updated!');
            } else {
                Storage::delete($uploaded);
                return Reply::error('Something went wrong!');
            }
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

        $this->pageTitle = 'KPI: Settings';
        $this->settings = Setting::all()->pluck('value', 'name');
        $this->allowedUsers = AllowedUser::all();
        $this->employees = Employee::exceptWriters()->active()->whereNotIn('id', $this->allowedUsers->pluck('user_id'))->get();

        return view('kpi::admin.settings', $this->data);
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
