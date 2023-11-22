<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('statistics', 'API\StatisticsController@get');