<?php

use App\Http\Controllers\ConvertController;
use Illuminate\Support\Facades\Route;

Route::post('/convert/{sourceLanguage}/to/{targetLanguage}', [ConvertController::class, 'convert']);
