<?php

namespace Modules\KPI\Http\Controllers\Admin;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\KPI\Entities\Setting;
use Illuminate\Routing\Controller;
use Modules\KPI\Entities\Employee;
use Illuminate\Support\Facades\Http;
use Modules\KPI\Entities\InfractionType;
use Modules\KPI\DataTables\RatingsDataTable;
use Modules\KPI\DataTables\EmployeesDataTable;
use Modules\KPI\DataTables\InfractionsDataTable;
use App\Http\Controllers\Admin\AdminBaseController;

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
    public function rating(RatingsDataTable $dataTable)
    {
        $this->pageTitle = 'Task Ratings & Ontime Scores';
        $this->employees = Employee::exceptWriters()->active()->get();
        
        return $dataTable->render('kpi::admin.rating', $this->data);
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
                'infraction_score']
            );

            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(['name' => $key], ['value' => $value]);
            }

            return back()->withSuccess('Settings updated successfully');
        }
        $this->pageTitle = 'KPI: Settings';
        $this->settings = Setting::all()->pluck('value', 'name');

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
