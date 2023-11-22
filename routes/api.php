<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\StatisticsController;

Route::get('statistics', 'API\StatisticsController@get');