<?php
namespace App\Http\Managers;

use App\Http\Response\HTTPStatus;

/**
 * Class ErrorManager
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Http\Managers
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 29.10.2020
 */
class ErrorManager
{

    /** @var HTTPStatus $httpStatus */
    private HTTPStatus $httpStatus;

    /**
     * @return HTTPStatus
     */
    public function getHttpStatus(): HTTPStatus
    {
        return $this->httpStatus;
    }

    /**
     * @param HTTPStatus $httpStatus
     * @return ErrorManager
     */
    public function setHttpStatus(HTTPStatus $httpStatus): ErrorManager
    {
        $this->httpStatus = $httpStatus;

        return $this;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function build(): \Illuminate\Http\JsonResponse
    {
        if ($this->httpStatus === null) {
            $httpStatus = new HTTPStatus(444, "Unknown");
            $this->setHttpStatus($httpStatus);
        }

        $response = [
            "success" => false,
            "@method" => \request()->route()[1]['as'],
            "status" => $this->httpStatus->getHttpStatusCode(),
            "statusText" => $this->httpStatus->getHttpStatusText()
        ];

        return response()->json($response);
    }
}
