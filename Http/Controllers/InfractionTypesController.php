<?php

namespace Modules\KPI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\KPI\Entities\InfractionType;
use App\Http\Controllers\Member\MemberBaseController;
use App\Helper\Reply;

class InfractionTypesController extends MemberBaseController
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $this->types = InfractionType::all();

        return view('kpi::infractions.types', $this->data);
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
        $request->validate([
            'name' => 'required|string',
            'reduction_points' => 'required|numeric',
        ]);
        $request['created_by'] = auth()->id();

        $type = InfractionType::create($request->all());

        return Reply::success('Infraction Type created successfully!');
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
        $type = InfractionType::findOrFail($id);

        return response()->json($type);
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
            'name' => 'required|string',
            'reduction_points' => 'required|numeric',
        ]);

        $type = InfractionType::findOrFail($id);
        $type->update($request->all());

        return Reply::success('Infraction Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $type = InfractionType::findOrFail($id);

        if ($type->infractions->count() > 0) {
            return Reply::error('Some infractions are connected to this type!');
        }

        $type->delete();

        return Reply::success('Infraction Type deleted successfully!');
    }
}
