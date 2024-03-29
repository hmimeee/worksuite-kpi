@extends('layouts.app')

@section('page-title')
<div class="row bg-title">
	<div class="col-lg-8 col-md-5 col-sm-6 col-xs-12">
		<h4 class="page-title"><i class="{{ $pageIcon ?? '' }}"></i> {{ $pageTitle ?? 'KPI: Settings' }}</h4>
	</div>
</div>
@endsection

@section('content')
<div class="row">
	<div class="col-lg-12 m-t-5">
		<div class="white-box">
			<div class="p-5">
                <iframe src="{{asset('laraview/#../user-uploads/'.$settings['kpidoc'])}}" width="100%" height="850px"></iframe>
			</div>
		</div>
	</div>
</div>
@endsection
