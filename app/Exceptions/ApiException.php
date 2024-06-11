<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiException extends Exception
{
    protected $code = 422;
    /**
     * Report the exception
     * @return void
     */
    public function report()
    {

    }

    /**
     * Render an exception as HTTP Response
     * @param Request $request
     * @return void
     */
    public function render($request)
    {
        return new JsonResponse([
            'message' => $this->message
        ], $this->code);
    }
}
