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
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
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
	<div class="col-lg-6 m-t-5">
		<div class="white-box">
			<div class="p-5">
				<form method="post">
					@csrf
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<span class="input-group-addon">Office Start Time</span>
								<input type="text" name="start_time" id="start-time" class="form-control"
									value="{{ $settings['start_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<span class="input-group-addon">Office End Time</span>
								<input type="text" name="end_time" id="end-time" class="form-control"
									value="{{ $settings['end_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<span class="input-group-addon">Break Time Start</span>
								<input type="text" name="start_break_time" id="start-break-time" class="form-control"
									value="{{ $settings['start_break_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group bootstrap-timepicker timepicker">
								<span class="input-group-addon">Break Time End</span>
								<input type="text" name="end_break_time" id="end-break-time" class="form-control"
									value="{{ $settings['end_break_time'] ?? now() }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Allowed Time</span>
								<input type="number" class="form-control" name="allowed_time"
									value="{{ $settings['allowed_time'] ?? '' }}">
									<span class="input-group-addon">Minutes</span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Attendance Score</span>
								<input type="number" class="form-control" name="attendance_score"
									value="{{ $settings['attendance_score'] ?? '' }}">
									<span class="input-group-addon">Points</span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Work Score</span>
								<input type="number" class="form-control" name="work_score"
									value="{{ $settings['work_score'] ?? '' }}">
									<span class="input-group-addon">Points</span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Infraction Score</span>
								<input type="number" class="form-control" name="infraction_score"
									value="{{ $settings['infraction_score'] ?? '' }}">
									<span class="input-group-addon">Points</span>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Tracker Email</span>
								<input type="email" class="form-control" name="tracker_mail"
									value="{{ $settings['tracker_mail'] ?? '' }}">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Tracker Key</span>
								<input type="text" class="form-control" name="tracker_key"
									value="{{ $settings['tracker_key'] ?? '' }}">
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Except Employee(s)</span>
								<select name="except_users[]" class="select2 select2-multiple " multiple="multiple">
									@foreach($allEmployees as $u)
										<option value="{{ $u->id }}"
											{{ isset($settings['except_users']) ? (in_array($u->id, explode(',', $settings['except_users'])) ? 'selected' : '') : '' }}>
											{{ $u->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					
					<div class="col-md-12">
						<div class="form-group">
							<input type="hidden" name="update_setting" value="true">
							<button class="btn btn-success btn-sm">Update</button>
						</div>
					</div>

					<div class="col-md-12">
						<div class="form-group">
							<div class="input-group">
								<span class="input-group-addon">Update System Data</span>
								<a href="javascript:;" class="input-group-addon bg-info b-0 text-white" id="update-scores">Update
									Scores</a>
								<a href="javascript:;" class="input-group-addon bg-primary b-0 text-white" id="update-attendance-data">Update
									Attendance Data</a>
							</div>
						</div>
					</div>
				</form>
				
				<form method="post" id="update-module-form" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon">Update Module</span>
							<input type="file" name="module" class="form-control" style="display: inline; padding-top: 3px;">
							<a href="javascript:;" class="input-group-addon bg-success b-0 text-white" id="update-module">Upload</a>
							<input type="hidden" name="update_module" value="true">
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="col-lg-6 m-t-5 white-box p-r-10">
		<div class="p-5">
			<h4 class="block-head">Allow Users to Access <a href="javascript:;" class="btn btn-sm btn-info" style="float:right;" id="add-employee-btn">Add New</a></h4>
			<div class="form-group" id="allow-users" style="display: none">
				<div class="input-group">
				<span class="input-group-addon">Employees</span>
				<select name="allowed_users[]" class="select2 select2-multiple " multiple="multiple" id="allowedUsers">
					@foreach($employees as $u)
						<option value="{{ $u->id }}">{{ $u->name }}</option>
					@endforeach
				</select>
				<a href="javascript:;" class="input-group-addon bg-success b-0 text-white" id="add-employee">Add Now</a>
				</div>
			</div>
			<table class="table table-bordered table-hover" id="team-leader-table">
				<thead>
					<tr>
						<th style="width: 50px;">#</th>
						<th>Name</th>
						<th style="width: 50px;">Action</th>
					</tr>
				</thead>
				<tbody>
					@foreach($allowedUsers as $u)
						<tr>
							<td>{{ $u->user_id }}</td>
							<td>{{ $u->user->name }}</td>
							<td>
								<a href="javascript:;" class="btn btn-xs btn-danger" id="Unlink" title="Unlink" data-id="{{ $u->user_id }}">
									<i class="fa fa-unlink"></i>
								</a>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection

@push('footer-script')
<script src="{{asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js')}}"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script type = "text/javascript" >
	$(".select2").select2({
		formatNoMatches: function () {
			return "{{ __('messages.noRecordFound') }}";
		}
	});

	$('#add-employee-btn').click(function () {
		btn = $(this);
		if (btn.text() === 'Cancel') {
			btn.text('Add New');
		} else {
			btn.text('Cancel');
		}
		btn.toggleClass('btn-success btn-inverse');
		$('#allow-users').toggle('show');
	})

	var $insertBefore = $('#insertBefore');
	$('#start-time, #end-time, #start-break-time, #end-break-time').timepicker({
		@if($global->time_format == 'H:i')
		showMeridian: false,
		@endif
	});

	$('#update-module').click(function (e) {
		if ($(this).prev().val() == '') {
			$.showToastr('Please select package file of KPI module!', 'error');
			return false;
		}

		btn = $(this);
		btn.text('Uploading...');
		formData = new FormData(document.getElementById('update-module-form'));
		$.ajax({
			type: 'POST',
			url: '{{route('admin.kpi.settings')}}',
			contentType: false,
			processData: false,
			data: formData,
			success: function (response) {
				btn.text('Upload');
				if (response.status == "success") {
					swal("Success!", response.message, "success");
					location.reload(true);
				} else {
					swal("Warning!", response.message, "warning");
				}
			}
		})
	})

	$('#add-employee').click(function (e) {
		btn = $(this);
		dt = $(this).prev().val();

		if (dt == null) {
			$.showToastr('Please select at least one user!', 'error');
			return false;
		}

		btn.text('Adding...');
		formData = new FormData(document.getElementById('update-module-form'));
		$.ajax({
			type: 'POST',
			url: '{{route('admin.kpi.settings')}}',
			data: {'allowed_users': dt, 'add_allowed_users': true, '_token': '{{csrf_token()}}'},
			success: function (response) {
				btn.text('Add Now');
				if (response.status == "success") {
					swal("Success!", response.message, "success");
					location.reload(true);
				} else {
					swal("Warning!", response.message, "warning");
				}
			}
		})
	})

	$('body #Unlink').click(function(e) {
		property = $(this).parent().parent();
		$.ajax({
			type: 'POST',
			url: '{{route('admin.kpi.settings')}}',
			data: {'allowed_user': $(this).data('id'), 'remove_permission': true, '_token': '{{csrf_token()}}'},
			success: function (response) {
				if (response.status == "success") {
					swal("Success!", response.message, "success");
					property.remove();
				} else {
					swal("Warning!", response.message, "warning");
				}
			}
		})
	})

	$('body #update-scores').click(function(e) {
		$.ajax({
			type: 'GET',
			url: '{{route('admin.kpi.settings')}}?update_scores=true',
			success: function (response) {
				if (response.status == "success") {
					swal("Success!", response.message, "success");
				} else {
					swal("Warning!", response.message, "warning");
				}
			}
		})
	})

	$('body #update-attendance-data').click(function(e) {
		$.ajax({
			type: 'GET',
			url: '{{route('admin.kpi.settings')}}?update_attendance_data=true',
			success: function (response) {
				if (response.status == "success") {
					swal("Success!", response.message, "success");
				} else {
					swal("Warning!", response.message, "warning");
				}
			}
		})
	})
</script>
@endpush
