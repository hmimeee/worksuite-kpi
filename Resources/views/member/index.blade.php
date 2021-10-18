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
@endpush

@section('content')
	<div class="row dashboard-stats front-dashboard">
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
	<div class="col-sm-12 col-md-4">
			<div class="panel panel-inverse">
				<div class="panel-heading">Your Scores</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-success-gradient">
											<i class="ti-stats-up"></i>
										</span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">Rank Position</span><br>
									<span
										class="counter">{{$employees->pluck('id')->search(auth()->id())+1}}</span>
								</div>
							</div>
						</div>

						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-success-gradient">
											<i class="ti-timer"></i>
										</span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">Attendance Score</span><br>
									<span
										class="counter">{{ \Modules\KPI\Entities\Employee::attendanceScore(auth()->id()) }}</span>
								</div>
							</div>
						</div>

						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-success-gradient">
											<i class="ti-layers"></i>
										</span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">Task Score</span><br>
									<span
										class="counter">{{ \Modules\KPI\Entities\Employee::taskScores(auth()->id()) }}</span>
								</div>
							</div>
						</div>

						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-success-gradient">
											<i class="ti-alert"></i>
										</span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">Infraction Score</span><br>
									<span
										class="counter">{{ \Modules\KPI\Entities\Employee::infractionScore(auth()->id()) }}</span>
								</div>
							</div>
						</div>

						<div class="white-box">
							<div class="row">
								<div class="col-xs-3">
									<div>
										<span class="bg-success-gradient">
											<i class="ti-plus"></i>
										</span>
									</div>
								</div>
								<div class="col-xs-9 text-right">
									<span class="text-muted counter">Total Score</span><br>
									<span
										class="counter">{{ \Modules\KPI\Entities\Employee::allScores(auth()->id())->total }}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-6 col-md-4">
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
										<span class="text-muted counter">{{ $item->user->name }}</span><br>
										<span
											class="counter">{{ $item->total_score }}</span>
									</div>
								</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm-6 col-md-4">
			<div class="panel panel-inverse">
				<div class="panel-heading">Top 5 Low Scorers</div>
				<div class="panel-wrapper collapse in">
					<div class="panel-body">
						@foreach($topScorers['total_low'] as $key => $item)
							<div class="white-box">
								<div class="row">
									<div class="col-xs-3">
										<div>
											<span class="bg-danger-gradient"><b>{{ $key+1 }}</b></span>
										</div>
									</div>
									<div class="col-xs-9 text-right">
										<span class="text-muted counter">{{ $item->user->name }}</span><br>
										<span
											class="counter">{{ $item->total_score }}</span>
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
									<th>Attendance Score (out of {{$settings['attendance_score'] ?? 0}})</th>
									<th>Work Score (out of {{$settings['work_score'] ?? 0}})</th>
									<th>Infraction Score (out of {{$settings['infraction_score'] ?? 0}})</th>
									<th>Total Score</th>
								</tr>
							</thead>
							<tbody>
								@if (!auth()->user()->hasKPIAccess)
								@php($employees = $employees->where('id', auth()->id()))
								@endif

								@foreach($employees as $employee)
									<tr>
										<td>{{$employees->pluck('id')->search($employee->id)+1}}</td>
										<td><a href="javascript:;" onclick="viewProfile('{{ $employee->id }}')">{{ $employee->name }}</a> {!!$employee->scores->total_score > 100 ? '<span class="label label-warning"><i class="ti-crown"></i></span>' : '' !!}</td>
										<td>
											{{ $employee->scores->attendance_score }}
										</td>
										<td>
											{{ $employee->scores->work_score }}
										</td>
										<td>
											{{ $employee->scores->infraction_score }}
										</td>
										<td>
											{{ $employee->scores->total_score }}
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

		{{--Ajax Modal--}}
<div class="modal fade bs-modal-md in" id="profileModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->.
</div>
{{--Ajax Modal Ends--}}
@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script>
	$(document).ready(function () {
		$('#employees-table').DataTable({
			"pageLength": 25
		});

		$('.kpi-tab').toggle('show');
		$('.kpi-btn').hover(function () {
			$('.kpi-tab').toggle('show');
		})
	});

	function viewProfile(id){
		url = '{{route('member.kpi.profile', ':id')}}?year={{ request()->year }}&month={{ request()->month }}';
		url = url.replace(':id', id);

		$.ajaxModal('#profileModal', url);
	}
</script>
@endpush
