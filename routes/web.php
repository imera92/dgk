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

Route::get('test', 'WebController@test');

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();

    // Database Routes
    Route::resource('db', 'Voyager\DatabaseController');

	// Metadata Routes
	Route::resource('metadata', 'Voyager\MetadataController');
    Route::group(['prefix' => 'metadata'], function () {
		Route::post('/tables', 'Voyager\MetadataController@tables');
		Route::post('/columns', 'Voyager\MetadataController@columns');
    });
});