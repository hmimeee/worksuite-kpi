<?php

namespace Modules\KPI\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KPI\Entities\Employee;
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
        
        return view('kpi::index', $this->data);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function infractions(InfractionsDataTable $dataTable)
    {
        $this->pageTitle = 'Infractions';

        return $dataTable->render('kpi::admin.infractions', $this->data);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function rating(EmployeesDataTable $dataTable)
    {
        $this->pageTitle = 'Task Ratings & KPI Scores';
        
        return $dataTable->render('kpi::admin.rating', $this->data);
    }

    // public function employees(EmployeesDataTable $dataTable)
    // {
    //     $this->employees = Employee::where('id', user()->id);

    //     if (user()->hasRole('admin')) {
    //         $this->employees = Employee::exceptWriters()->active()->get();
    //     }

    //     return
    // }

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
