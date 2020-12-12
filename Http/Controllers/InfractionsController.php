<?php

namespace Modules\KPI\Http\Controllers;

use App\User;
use App\Helper\Reply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KPI\Entities\Infraction;
use Modules\KPI\Entities\InfractionType;
use Illuminate\Support\Facades\Notification;
use Modules\KPI\DataTables\InfractionsDataTable;
use App\Http\Controllers\Member\MemberBaseController;
use Modules\KPI\Entities\Employee;
use Modules\KPI\Notifications\InfractionNotification;

class InfractionsController extends MemberBaseController
{
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $this->employees = Employee::exceptWriters()->active()->get();
        $this->types = InfractionType::all();

        return view('kpi::infractions.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $infractionType = InfractionType::find($request->infraction_type_id);

        $infraction = new Infraction();
        $infraction->created_by = auth()->id();
        $infraction->user_id = $request->user_id;
        $infraction->details = $request->details;
        if ($request->from_list && $request->infraction_type_id) {
            $infraction->infraction_type_id = $request->infraction_type_id;
            $infraction->reduction_points = $infractionType->reduction_points;
        } elseif ($request->infraction_type) {
            $infraction->infraction_type = $request->infraction_type;
            $infraction->reduction_points = $request->reduction_points;
        } else {
            return Reply::error('You must write or select infraction type!');
        }

        if (!$infraction->save()) {
            return Reply::error('Something went wrong!');
        }

        $notifyTo = Employee::find($request->user_id);
        Notification::send($notifyTo, new InfractionNotification($infraction, 'Added New Infraction', 'added a new infraction against you.'));
        return Reply::success('Infraction created successfully!');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        $this->infraction = Infraction::find($id);
        
        return view('kpi::infractions.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $this->employees = User::allEmployees();
        $this->types = InfractionType::all();
        $this->infraction = Infraction::find($id);

        return view('kpi::infractions.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'infraction_type_id' => 'required_with:from_list|exists:kpi_infraction_types,id',
            'reduction_points' => 'required_without:from_list|numeric',
        ]);

        $infraction = Infraction::find($id);

        $infraction->user_id = $request->user_id;

        if ($request->from_list && $request->infraction_type_id) {
            $infraction->infraction_type_id = $request->infraction_type_id;

            $type = InfractionType::find($request->infraction_type_id);
            $infraction->reduction_points = $type->reduction_points;
        } elseif ($request->infraction_type) {
            $infraction->infraction_type_id = null;
            $infraction->infraction_type = $request->infraction_type;
            $infraction->reduction_points = $request->reduction_points;
        } else {
            return Reply::error('You must write or select infraction type!');
        }

        if ($request->details) {
            $infraction->details = $request->details;
        }

        $infraction->save();

        return Reply::success('Infraction updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $infraction = Infraction::find($id);
        $infraction->delete();

        return Reply::success('Infraction deleted successfully!');
    }
}
