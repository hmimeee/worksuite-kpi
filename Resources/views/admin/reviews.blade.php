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
	<div class="col-md-12">
		<div class="white-box">
			<div class="p-10">
				<div class="table-responsive">
					{!! $dataTable->table(['class' => 'table table-bordered table-hover toggle-circle default footable-loaded footable']) !!}
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
	function viewInfraction(id){
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

	// $('#apply-filters').click(function () {
	// 	$('#candidates-table').on('preXhr.dt', function (e, settings, data) {
	// 		var status = $('#status').val();
	// 		data['status'] = status;
	// 	});

	// 	$.easyBlockUI('#candidates-table');
		// window.LaravelDataTables["candidates-table"].draw();
	// 	$.easyUnblockUI('#candidates-table');

	// });

	$()
</script>
@endpush
