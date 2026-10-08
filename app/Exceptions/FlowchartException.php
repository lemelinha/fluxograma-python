<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FlowchartException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        //
    }
}
