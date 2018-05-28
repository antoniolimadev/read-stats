<?php


Route::get('/', 'StatsController@index');
Route::get('/userstats/{user}', 'StatsController@stats');
Route::post('/userstats', 'StatsController@generate');
Route::get('/userstats', 'StatsController@index');



