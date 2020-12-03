@extends('layouts.app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle }}</h4>
	</div>
</div>
@endsection

@push('head-script')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
@endpush

@section('content')
	<div class="row dashboard-stats front-dashboard">
		@section('filter-section')
	<form>
		<div class="form-group">
			<label>Select month</label>
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
			<label>Select month</label>
			<select name="month" id="month" class="form-control">
				@for($i = 1 ; $i <= 12; $i++)
					<option value="{{ $i }}"
						{{ request('month') == $i ? 'selected' : '' }}
						{{ request('month') == null && $i == date('m') ? 'selected' : '' }}>
						{{ date("F",strtotime((request()->year ?? date("Y"))."-".$i."-01")) }}
					</option>
				@endfor
			</select>
		</div>

		<div class="form-group">
			<button class="btn btn-success btn-sm">Apply</button>
			<a href="{{ request()->url() }}" class="btn btn-inverse btn-sm">Reset</a>
		</div>
	</form>
	@endsection

		<div class="p-5">
		</div>
		<div class="col-md-3">
			<div class="panel panel-inverse">
				<div class="panel-heading">Top 5 High Scorers</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						@foreach($topScorers['total'] as $key => $item)
							<div class="white-box">
								<div class="row">
									<div class="col-xs-3">
										<div>
											<span class="bg-success-gradient"><b>{{ $key+1 }}</b></span>
										</div>
									</div>
									<div class="col-xs-9 text-right">
										<span class="text-muted counter">{{ $item->name }}</span><br>
										<span
											class="counter">{{ Modules\KPI\Entities\Employee::allScores($item->id)->total }}</span>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="panel panel-inverse">
				<div class="panel-heading">Top 5 Attendance Scorers</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						@foreach($topScorers['attendance'] as $key => $item)
						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-danger-gradient"><b>{{$key+1}}</b></span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">{{ $item->name }}</span><br>
									<span
										class="counter">{{ Modules\KPI\Entities\Employee::attendanceScore($item->id) }}</span>
								</div>
							</div>
						</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-3">
			<div class="panel panel-inverse">
				<div class="panel-heading">Top 5 Work Scorers</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						@foreach($topScorers['work'] as $key => $item)
						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-warning-gradient"><b>{{$key+1}}</b></span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">{{ $item->name }}</span><br>
									<span
										class="counter">{{ Modules\KPI\Entities\Employee::taskScores($item->id) }}</span>
								</div>
							</div>
						</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-3">
			<div class="panel panel-inverse">
				<div class="panel-heading">Top 5 Infraction Scorers</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						@foreach($topScorers['infraction'] as $key => $item)
						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-danger-gradient"><b>{{$key+1}}</b></span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">{{ $item->name }}</span><br>
									<span
										class="counter">{{ Modules\KPI\Entities\Employee::infractionScore($item->id) }}</span>
								</div>
							</div>
						</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-12">
			<div class="panel panel-inverse">
				<div class="panel-heading">KPI Scores</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						<table class="table table-bordered table-hover" id="employees-table">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Attendance Score (Out of 40)</th>
									<th>Infraction Score (Out of 20)</th>
									<th>Work Score (Out of 20)</th>
									<th>Total Score</th>
								</tr>
							</thead>
							<tbody>
								@foreach($employees as $employee)
									<tr>
										<td>{{ $employee->id }}</td>
										<td><a href="javascript:;">{{ $employee->name }}</a></td>
										<td>
											{{ Modules\KPI\Entities\Employee::attendanceScore($employee->id) }}
										</td>
										<td>
											{{ Modules\KPI\Entities\Employee::infractionScore($employee->id) }}
										</td>
										<td>
											{{ Modules\KPI\Entities\Employee::taskScores($employee->id) }}
										</td>
										<td>
											{{ Modules\KPI\Entities\Employee::allScores($employee->id)->total }}
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script>
	$(document).ready(function () {
		$('#employees-table').DataTable({
			"pageLength": 25
		});
	});
</script>
@endpush
