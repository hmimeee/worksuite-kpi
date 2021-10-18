<style type="text/css">
    .border {
        border: 1px solid rgba(0,0,0,0.2);
    }

    .block {
        height: 120px;
        padding: 2px;
    }
</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title">
        <i class="ti-id-badge"></i> Employee Statistics
    </h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <h3><b>{{ $employee->name }} {!!$employee->scores->total_score > 100 ? '<i class="ti-crown text-warning"></i>' : '' !!}</b></h3>
                <p>
                    @php($class = ['bad' => 'danger', 'medium' => 'warning', 'good' => 'success', 'best' => 'primary'])
                    {!! '<span class="m-r-5 btn cursor-pointer btn-xs btn-'.$class[$performance].'">'.ucwords($performance).' Performance</span>' !!}
                    {!! $rating !!} ({{$employee->scores->rating}} out of 5.0)
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 border block" align="center">
            <label>Attendance Score (out of {{$settings['attendance_score']}})</label>
            <div id="attendanceScore"></div>
        </div>
        <div class="col-md-3 border block" align="center">
            <label>Work Score (out of {{$settings['work_score']}})</label>
            <div id="workScore"></div>
        </div>
        <div class="col-md-3 border block" align="center">
            <label>Infraction Score (out of {{$settings['infraction_score']}})</label>
            <div id="infractionScore"></div>
        </div>
        <div class="col-md-3 border block" align="center">
            <label>Total Score (out of {{$employee->scores->out_of}})</label>
            <div id="totalScore"></div>
        </div>
    </div>
    <div class="row m-t-20">
        <div class="col-md-3 border block" align="center">
            <div class="form-group">
                <label>Rank Position</label><br/>
                <h1 class="text-info">
                    <b>{{$employees->pluck('id')->search($employee->id)+1}}</b>
                </h1>
            </div>
        </div>
        <div class="col-md-3 border block" align="center">
            <div class="form-group">
                <label>Total Worked Tasks</label><br/>
                <h1 class="text-info">
                    <b>{{$employee->getCompletedTasks->count()}}</b>
                </h1>
            </div>
        </div>
        <div class="col-md-3 border block" align="center">
            <div class="form-group">
                <label>Total Worked Articles</label><br/>
                <h1 class="text-info">
                    <b>{{$employee->completedArticles->count()}}</b>
                </h1>
            </div>
        </div>
        <div class="col-md-3 border block" align="center">
            <div class="form-group">
                <label>Total Attendance</label><br/>
                <h1 class="text-info">
                    <b>{{$employee->getAttendances->count()}}</b>
                </h1>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-white waves-effect" data-dismiss="modal">@lang('app.close')</button>
</div>

<script src="{{ asset('plugins/bower_components/peity/jquery.peity.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/peity/jquery.peity.init.js') }}"></script>
<script src="{{asset('js/circles.min.js')}}"></script>
<script>
    function makeCircle(id, value, max = 100, color = '#00c292'){
        return Circles.create({
            maxValue:     max,
            id:           id,
            value:        value,
            radius:       45,
            width:        13,
            duration:     1,
            colors:       ['#dedede', color]
        });
    }

    makeCircle('attendanceScore', '{{$employee->scores->attendance_score ?? 0}}', '{{$employee->scores->attendance_score > $settings['attendance_score'] ? $employee->scores->attendance_score : $settings['attendance_score']}}');
    makeCircle('workScore', '{{$employee->scores->work_score ?? 0}}', '{{$employee->scores->work_score > $settings['work_score'] ? $employee->scores->work_score : $settings['work_score']}}', '#f6d365');
    makeCircle('infractionScore', '{{$employee->scores->infraction_score ?? 0}}', '{{$employee->scores->infraction_score > $settings['infraction_score'] ? $employee->scores->infraction_score : $settings['infraction_score']}}', '#66a6ff');
    makeCircle('totalScore', '{{$employee->scores->total_score ?? 0}}', '{{ $employee->scores->total_score > 100 ? $employee->scores->total_score : $employee->scores->out_of }}', '#ab8ce4');
</script>