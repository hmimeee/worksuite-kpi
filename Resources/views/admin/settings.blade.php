@extends('layouts.app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle ?? 'KPI: Settings' }}</h4>
	</div>
</div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">
@endpush

@section('content')
<div class="row">
	@if(session()->has('success'))
		<div class="col-lg-12 m-t-10">
			<div class="alert alert-success dismissable">
				<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
				{{ session()->get('success') }}
			</div>
		</div>
	@endif
	<form method="post">
		@csrf
		<div class="col-lg-4 m-t-5">
			<div class="white-box">
				<div class="p-10">
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<label class="required">Office Start Time</label>
								<input type="text" name="start_time" id="start-time" class="form-control" value="{{ $settings['start_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<label class="required">Office End Time</label>
								<input type="text" name="end_time" id="end-time" class="form-control" value="{{ $settings['end_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<label class="required">Break Time Start</label>
								<input type="text" name="start_break_time" id="start-break-time" class="form-control" value="{{ $settings['start_break_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<label class="required">Break Time End</label>
								<input type="text" name="end_break_time" id="end-break-time" class="form-control" value="{{ $settings['end_break_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">Attendance Score</span>
							<input type="number" class="form-control" name="attendance_score" value="{{$settings['attendance_score'] ?? ''}}">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">Work Score</span>
							<input type="number" class="form-control" name="work_score" value="{{$settings['work_score'] ?? ''}}">
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">Infraction Score</span>
							<input type="number" class="form-control" name="infraction_score" value="{{$settings['infraction_score'] ?? ''}}">
						</div>
					</div>
					<div class="form-group" align="right">
						<input type="hidden" name="update_setting" value="true">
						<button class="btn btn-success btn-sm">Update</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
@endsection

@push('footer-script')
<script src="http://worksuite.test/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
<script src="http://worksuite.test/plugins/bower_components/timepicker/bootstrap-timepicker.min.js"></script>
<script type="text/javascript">
var $insertBefore = $('#insertBefore');
    $('#start-time, #end-time, #start-break-time, #end-break-time').timepicker({
        @if($global->time_format == 'H:i')
        showMeridian: false,
        @endif
    });
</script>
@endpush
