@extends('layouts.app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle }}</h4>
	</div>

	<div class="col-lg-4 col-sm-6 col-md-7 col-xs-12 text-right">
		<a href="javascript:;" class="btn btn-outline btn-success btn-sm" id="addInfraction">Add Infraction <i class="fa fa-plus" aria-hidden="true"></i></a>
		<a href="javascript:;" class="btn btn-outline btn-info btn-sm" id="infractionTypes"> Infraction Types</a>
	</div>
</div>
@endsection

@push('head-script')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.13/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/custom-select/custom-select.css') }}">
@endpush

@section('content')
<div class="row">
@section('filter-section')
<form>
	<div class="form-group">
		<label>Select year</label>
		<select name="year" id="year" class="form-control">
			@foreach(range(date("Y"), 2019) as $year)
				<option value="{{ $year }}"
					{{ request('year') == $year ? 'selected' : '' }}
					{{ request('year') == null && $year == date('Y') ? 'selected' : '' }}>
					{{ $year }}
				</option>
			@endforeach
		</select>
	</div>

	<div class="form-group">
		<label>Select month of {{ date('Y') }}</label>
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
	</div>
</form>
@endsection
<div class="col-md-5">
	<div class="white-box">
		<div class="p-10">
			<h4 class="block-head">Statistics</h4>
			<table class="table table-bordered table-hover" id="employees-table">
				<thead>
					<tr>
						<th>#</th>
						<th>Name</th>
						<th>Score (out of {{$settings['infraction_score'] ?? 0}})</th>
					</tr>
				</thead>
				<tbody>
					@foreach($employees as $employee)
						<tr>
							<td>{{ $employee->id }}</td>
							<td><a href="javascript:;" onclick="userInfractions('{{ $employee->id }}')">{{ $employee->name }}</a></td>
							<td>
								{{Modules\KPI\Entities\Employee::infractionScore($employee->id)}}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="col-md-7">
	<div class="white-box">
		<div class="p-10">
			<h4 class="block-head">Infractions</h4>
			<div class="table-responsive">
				{!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable']) !!}
				<input type="hidden" name="employee" value="{{auth()->id()}}" id="employeeId">
			</div>
		</div>
	</div>
</div>
</div>

{{--Ajax Modal--}}
<div class="modal fade bs-modal-lg in" id="infractionModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
{{--Ajax Modal Ends--}}
@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/datatables/jquery.dataTables.min.js') }}"></script>
<script src="https://cdn.datatables.net/1.10.13/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.1.1/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('plugins/bower_components/custom-select/custom-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="https://cdn.datatables.net/buttons/1.0.3/js/dataTables.buttons.min.js"></script>
<script src="{{ asset('js/datatables/buttons.server-side.js') }}"></script>

{!! $dataTable->scripts() !!}

<script>

	$('#infractions-table').on('preXhr.dt', function (e, settings, data) {
		data['employee'] = $('#employeeId').val();
		data['length'] = 25;
		data['month'] = $('#month').val();
	});

	function reloadTable() {
		window.LaravelDataTables["infractions-table"].draw();
	}

	function userInfractions(id) {
		$('#employeeId').val(id);
		reloadTable();
	}

	function viewInfraction(id) {
		url = '{{ route('admin.kpi.infractions.show', ':id')}}';
		url = url.replace(':id', id);

		$('#modelHeading').html("Infraction Details");
		$.ajaxModal('#infractionModal', url);
	}

	function editInfraction(id){
		url = '{{ route('admin.kpi.infractions.edit', ':id')}}';
		url = url.replace(':id', id);

		$('#modelHeading').html("Infraction Edit");
		$.ajaxModal('#infractionModal', url);
	}

	function deleteInfraction(id){
		url = '{{ route('admin.kpi.infractions.destroy', ':id')}}';
		url = url.replace(':id', id);

		swal({
			title: "Are you sure?",
			text: "You will not be able to recover the deleted data!",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Yes, delete it!",
			cancelButtonText: "No, cancel please!",
			closeOnConfirm: true,
			closeOnCancel: true
		}, function (isConfirm) {
			if (isConfirm) {
				var token = "{{ csrf_token() }}";

				$.easyAjax({
					type: 'POST',
					url: url,
					data: {'_token': token, '_method': 'DELETE'},
					success: function (response) {
						if (response.status == "success") {
							window.LaravelDataTables["infractions-table"].draw();
						}
					}
				});
			}
		});
	}

	$('#addInfraction').click(function(){
		var url = '{{ route('admin.kpi.infractions.create')}}';
		$('#modelHeading').html("Add New Infraction");
		$.ajaxModal('#infractionModal', url);
	});

	$('#infractionTypes').click(function(){
		var url = '{{ route('admin.kpi.infraction-types.index')}}';
		$('#modelHeading').html("Infraction Types");
		$.ajaxModal('#infractionModal', url);
	});

	$(".select2").select2();
	
	$(document).ready(function () {
			$('#employees-table').DataTable({
				"pageLength": 25
			});
		});
</script>
@endpush
