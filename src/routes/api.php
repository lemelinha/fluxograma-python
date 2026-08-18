<?php

use App\Http\Controllers\ConverterController;
use Illuminate\Support\Facades\Route;

$languages = ['flowchart', 'python'];

Route::get('/{source}/to/{target}', [ConverterController::class, 'convert'])
    ->whereIn('source', $languages)
    ->whereIn('target', $languages);
