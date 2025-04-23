<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

class OverbookingException extends Exception
{
    //protected $message = 'The event is fully booked.';
    // public function __construct()
    // {
    //     parent::__construct(409, 'The event is fully booked.');
    // }
    public function render($request)
    {
        return response()->json([
            'message' => 'The event is fully booked.',
        ], 409);
    }
}
