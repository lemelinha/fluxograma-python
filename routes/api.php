<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConvertController;

Route::get('/convert/{sourceLanguage}/to/{targetLanguage}', [ConvertController::class, 'convert']);
