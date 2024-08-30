<?php

namespace App\Exceptions;

use App\Traits\JSONResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class CustomUnauthorizedException extends Exception
{

    use JSONResponseTrait;
    /**
     * Create a new exception instance.
     *
     * @param string $message
     * @return void
     */
    public function __construct(string $message = 'Vous n\'avez pas les permissions nécessaires.')
    {
        parent::__construct($message);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request  $request
     * @return Response
     */
    public function render($request)
    {
        return $this->errorResponse($this->getMessage(), Response::HTTP_FORBIDDEN);
    }
}
