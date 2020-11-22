@extends('layouts.app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle }}</h4>
	</div>

	<div class="col-lg-4 col-sm-6 col-md-7 col-xs-12 text-right">
		<a href="javascript:;" class="btn btn-outline btn-success btn-sm" id="addCandidate">Add Infraction <i class="fa fa-plus" aria-hidden="true"></i></a>
		</div>
	</div>
	@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="white-box">
			<div class="p-10">
				Test
			</div>
		</div>
	</div>
</div>
@endsection
