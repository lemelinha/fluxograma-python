<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConverterController;


$languages = ["flowchart", "python"];

Route::get('/{source}/to/{target}', function (Request $request, string $source, string $target) {
    if ($source==$target) {
        return response()->json(['status' => 'error', 'message' => 'source and target are equals'], 400);
    }
    return app(ConverterController::class)->convert($request, $source, $target);
})->whereIn('source', $languages)->whereIn('target', $languages);
