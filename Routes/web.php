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
Route::group(['prefix' => 'admin/kpi', 'as' => 'admin.kpi.'], function () {

	Route::get('overview', 'Admin\AdminPanelController@index')->name('overview');
	Route::get('infractions', 'Admin\AdminPanelController@infractions')->name('infractions.index');
	Route::get('ratings', 'Admin\AdminPanelController@rating')->name('rating.index');
	Route::get('get-tasks', 'Admin\AdminPanelController@tasks')->name('rating.tasks');
	Route::get('attendances', 'Admin\AdminPanelController@attendances')->name('attendances.index');
	Route::get('attendances/user-data/{user}', 'Admin\AdminPanelController@userData')->name('attendances.userData');

	//Infractions routes
	Route::resource('infractions', 'InfractionsController', ['except' => ['index']]);

	//Infraction types routes
	Route::resource('infraction-types', 'InfractionTypesController');

});