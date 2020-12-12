@extends('layouts.member-app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle }}</h4>
	</div>
</div>
@endsection

@push('head-script')
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
	<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
	<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
	<link rel="stylesheet"
		href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
	<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endpush

@section('content')
<div class="row">
	@section('filter-section')
	<form>
		<div class="form-group">
			<select name="year" id="year" class="form-control">
				@foreach (range(date("Y"), 2019) as $year)
					<option value="{{ $year }}"
						{{ request('year') == $year ? 'selected' : '' }}
						{{ request('year') == null && $year == date('Y') ? 'selected' : '' }}>
						{{ $year }}
					</option>
				@endforeach
			</select>
		</div>

		<div class="form-group">
			<select name="month" id="month" class="form-control">
				@for($i = 1 ; $i <= 12; $i++)
					<option value="{{ $i }}"
						{{ request('month') == $i ? 'selected' : '' }}
						{{ request('month') == null && $i == date('m') ? 'selected' : '' }}>
						{{ date("F",strtotime(date("Y")."-".$i."-01")) }}
					</option>
				@endfor
			</select>
		</div>

		<div class="form-group">
			<button class="btn btn-success btn-sm">Apply</button>
			<a href="{{ request()->url() }}" class="btn btn-inverse btn-sm">Reset</a>
			<input type="hidden" name="employee" value="{{ request()->employee ?? auth()->id() }}"
				id="userData">
		</div>
	</form>
	@endsection
	<div class="col-lg-5">
		<div class="white-box">
			<div class="p-10">
				<h4 class="block-head">Statistics</h4>
				<table class="table table-bordered table-hover" id="employees-table">
					<thead>
						<tr role="row">
							<th>#</th>
							<th>Name</th>
							<th>Attended Days</th>
							{{-- <th>Total Logged</th> --}}
							<th>Score (out of {{$settings['attendance_score'] ?? 0}})</th>
						</tr>
					</thead>
					<tbody id="list">
						@foreach($employees as $employee)
						@php($log = $logData->where('email', $employee->email))
							<tr>
								<td>{{$employee->id }}</td>
								<td><a href="javascript:;" onclick="userData('{{$employee->id}}', '{{ $employee->name }}')">{{ $employee->name }}</a></td>
								<td>
									{{ $log->count() }}
								</td>
								{{-- <td>
									@php($hours = $log->sum('minutes')/60)
									@php($minutes = $hours - floor($hours))
									{{floor($hours)}} hours {{round($minutes*60)}} minutes
								</td> --}}
								<td>
									{{$employee->scores->attendance_score}}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-lg-7">
		<div class="white-box">
			<div class="p-10">
				<h4 class="block-head"><span class="text-info font-bold" id="userName">{{\App\User::find(request()->employee)->name ?? auth()->user()->name}}</span>'s Attendances</h4>
				<table class="table table-bordered table-hover" id="attendances-table">
					<thead>
						<tr>
							<th>Date</th>
							<th>Start Time</th>
							<th>Break Start</th>
							<th>Break End</th>
							<th>End Time</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>

{{-- Ajax Modal --}}
<div class="modal fade bs-modal-lg in" id="infractionModal" role="dialog" aria-labelledby="myModalLabel"
	aria-hidden="true">
	<div class="modal-dialog modal-lg" id="modal-data-application">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<span class="caption-subject font-red-sunglo bold uppercase" id="modelHeading"></span>
			</div>
			<div class="modal-body">
				Loading...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn default" data-dismiss="modal">Close</button>
				<button type="button" class="btn blue">Save changes</button>
			</div>
		</div>
	</div>
</div>
{{-- Ajax Modal Ends --}}
@endsection

@push('footer-script')
	<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}">
	</script>
	<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>

	<script>
		$(document).ready(function () {
			$('#employees-table').DataTable({
				"pageLength": 25
			});

			tableReload();
		});

		function tableReload() {
			url = '{{route('member.kpi.attendances.userData', ':id')}}';
			url = url.replace(':id', $('#userData').val());
			$.ajax({
				method: 'GET',
				url: url,
				data: {
					'month': '{{request()->month}}',
					'year': '{{request()->year}}',
					'array': true,
				},
				success: function (res) {
					timeTable(res);
				}
			});
		}

		function userData(id, name = null) {
			$('#userData').val(id);
			$('#userName').text(name);
			$('#attendances-table').DataTable().clear().destroy();
			tableReload();
		}

		function timeTable(data) {
			$('#attendances-table').DataTable({
				"pageLength": 25,
				"data": data,
				columns: [{
						data: 'date'
					},
					{
						data: 'start'
					},
					{
						data: 'break_start'
					},
					{
						data: 'break_end'
					},
					{
						data: 'end'
					}
				]
			});
		}
	</script>
@endpush
