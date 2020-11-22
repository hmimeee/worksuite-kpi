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

	//Infractions routes
	Route::resource('infractions', 'InfractionsController', ['except' => ['index']]);

	//Infraction types routes
	Route::resource('infraction-types', 'InfractionTypesController');

});