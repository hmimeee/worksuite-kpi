@extends('layouts.app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle }}</h4>
	</div>

	<div class="col-lg-4 col-sm-6 col-md-7 col-xs-12 text-right">
		<a href="javascript:;" class="btn btn-outline btn-success btn-sm" id="addInfraction">Add Infraction <i
				class="fa fa-plus" aria-hidden="true"></i></a>
		<a href="javascript:;" class="btn btn-outline btn-info btn-sm" id="infractionTypes"> Infraction Types</a>
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
	<div class="col-lg-6">
		<div class="white-box">
			<div class="p-10">
				<h4 class="block-head">Statistics</h4>
				<div class="table-responsive">
					{!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable']) !!}
					<input type="hidden" name="employee" value="{{auth()->id()}}" id="employeeId">
				</div>
				{{-- <table class="table table-bordered table-hover">
					<thead>
						<tr role="row">
							<th>#</th>
							<th>Name</th>
							<th>Rating</th>
							<th>Ontime Score (Out of 20)</th>
						</tr>
					</thead>
					<tbody id="list">
						@foreach($employees as $employee)
							<tr>
								<td>{{$employee->id }}</td>
								<td><a href="javascript::" id="userTasks" data-id="{{$employee->id }}">{{ $employee->name }}</a></td>
								<td>
									@php($html = Modules\KPI\Entities\Employee::taskRating($employee->id))
									{!!$html!!}
								</td>
								<td>
									@php($score = Modules\KPI\Entities\Employee::taskScores($employee->id))
									{!!$score!!}
								</td>
							</tr>
						@endforeach
					</tbody>
				</table> --}}
			</div>
		</div>
	</div>

	<div class="col-lg-6">
		<div class="white-box">
			<div class="p-10">
				<h4 class="block-head">User Tasks</h4>
				
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
	<script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
	<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}">
	</script>
	<script
		src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}">
	</script>
	<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
	<script src="{{ asset('js/datatables/buttons.server-side.js') }}"></script>

	{!! $dataTable->scripts() !!}

	<script>
	function userTasks(id) {
		$('#employeeId').val(id);
		window.scrollTo({
			top: 0,
			behavior: 'smooth'
		});
		// $(window).scrollTop(0);
		reloadTable();
	};

		function showTask(id) {
			$(".right-sidebar").slideDown(50).addClass("shw-rside");
			url = '{{ route('admin.all-tasks.show', ':id') }}';
			url = url.replace(':id', id);

			$.easyAjax({
				type: 'GET',
				url: url,
				success: function (response) {
					if (response.status == "success") {
						$('#right-sidebar-content').html(response.view);
					}

					$("body").tooltip({
						selector: '[data-toggle="tooltip"]'
					});
				}
			});
		}

		 $('#employees-table').on('preXhr.dt', function (e, settings, data) {
			data['employee'] = $('#employeeId').val();
			data['length'] = 20;
			data['month'] = null;
		 });

		function reloadTable() {
			window.LaravelDataTables["employees-table"].draw();
		}
	</script>
@endpush
