<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Admin panel routes
Route::group(['prefix' => 'admin/kpi', 'as' =>'admin.kpi.', 'middleware' => ['auth']], function () {

	Route::get('overview', 'Admin\AdminPanelController@index')->name('overview');
	Route::get('infractions', 'Admin\AdminPanelController@infractions')->name('infractions.index');
	Route::get('ratings', 'Admin\AdminPanelController@rating')->name('rating.index');
	Route::get('get-tasks', 'Admin\AdminPanelController@rating')->name('rating.tasks');
	Route::get('attendances', 'Admin\AdminPanelController@attendances')->name('attendances.index');
	Route::get('attendances/user-data/{user}', 'Admin\AdminPanelController@userData')->name('attendances.userData');
	Route::any('settings', 'Admin\AdminPanelController@settings')->name('settings');

	//Employee profile routes
	Route::get('profile/{user}', 'Admin\AdminPanelController@profile')->name('profile');

	//Documentation
	Route::get('documentation', 'Admin\AdminPanelController@doc')->name('doc');

	//Infractions routes
	Route::resource('infractions', 'InfractionsController', ['except' => ['index']]);

	//Infraction types routes
	Route::resource('infraction-types', 'InfractionTypesController');

	Route::any('testing', 'Admin\AdminPanelController@testing')->name('tesing');
});

//Member panel routes
Route::group(['prefix' => 'member/kpi', 'as' =>'member.kpi.', 'middleware' => ['auth']], function () {

	Route::get('overview', 'Member\MemberPanelController@index')->name('overview');
	Route::get('infractions', 'Member\MemberPanelController@infractions')->name('infractions.index');
	Route::get('ratings', 'Member\MemberPanelController@rating')->name('rating.index');
	Route::get('get-tasks', 'Member\MemberPanelController@rating')->name('rating.tasks');
	Route::get('attendances', 'Member\MemberPanelController@attendances')->name('attendances.index');
	Route::get('attendances/user-data/{user}', 'Member\MemberPanelController@userData')->name('attendances.userData');

	//Employee profile routes
	Route::get('profile/{user}', 'Member\MemberPanelController@profile')->name('profile');

	//Documentation
	Route::get('documentation', 'Member\MemberPanelController@doc')->name('doc');

	//Infractions routes
	Route::resource('infractions', 'InfractionsController', ['except' => ['index']]);

	//Infraction types routes
	Route::resource('infraction-types', 'InfractionTypesController');
});