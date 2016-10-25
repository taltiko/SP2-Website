<?php

Route::get('/route-info', 'PagesController@getRouteInfo');
Route::get('/trein-info', 'PagesController@getTreinInfo');
Route::get('/station-info', 'PagesController@getStationInfo');
Route::get('/tarieven', 'PagesController@getTarieven');
Route::get('/contact', 'PagesController@getContact');
Route::get('/', 'PagesController@getIndex');
Route::post('/route-info/zoek', ['as' => 'route-info.zoek', 'uses' => 'InfoController@getRouteInfo']);
Route::post('/trein-info/zoek', ['as' => 'trein-info.zoek', 'uses' => 'InfoController@getTreinInfo']);
Route::post('/station-info/zoek', ['as' => 'station-info.zoek', 'uses' => 'InfoController@getStationInfo']);

Route::get('/httpexception/{code}', function($code){
		$link = "errors." . $code;
		return view($link);
});