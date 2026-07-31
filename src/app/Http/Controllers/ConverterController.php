<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FlowchartToAstService;

class ConverterController extends Controller
{
    public function __construct(
        protected FlowchartToAstService $FlowchartToAstService
    ){}

    public function convert(Request $request, string $source, string $target)
    {
        dd($this);
        return ['source' => $source, 'target' => $target];
    }
}
