<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Infraction Details</h4>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <div class="row">
            <div class="col-xs-12" >
                <div class="col-xs-6 col-md-3 font-12">
                    <label class="font-12">Employee</label><br>
                    <img src="{{ $infraction->user->image_url }}" class="img-circle" width="25" height="25"> 
                    <a href="{{route('admin.employees.show', $infraction->user->id)}}">{{$infraction->user->name}}</a>
                </div>

                <div class="col-xs-6 col-md-3 font-12">
                    <label class="font-12">Infraction Type</label><br>
                    {!! $infraction->type ? '<label class="label label-info">'.$infraction->type->name.'</label>' : '<label class="label label-inverse">'.$infraction->infraction_type.'</label>' !!}
                </div>

                <div class="col-xs-6 col-md-3 font-12">
                    <label class="font-12">Reduction Points</label><br>
                    {{$infraction->reduction_points}}
                </div>

                <div class="col-xs-6 col-md-3 font-12">
                    <label class="font-12">Date</label><br>
                    {{$infraction->created_at->format('d M Y')}}
                </div>
            </div>

            @if($infraction->type)
            <div class="col-xs-12 m-t-20 p-10">
                <label class="font-12">Infraction Details</label>
                <div class="task-description b-all p-10">
                    {!! $infraction->type->details !!}
                </div>
            </div>
            @endif

            @if($infraction->details)
            <div class="col-xs-12 m-t-20 p-10">
                <label class="font-12">Infraction Notes</label>
                <div class="task-description b-all p-10">
                    {!! $infraction->details !!}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>