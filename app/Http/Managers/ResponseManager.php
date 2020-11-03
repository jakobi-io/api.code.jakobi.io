<?php
namespace App\Http\Managers;

use App\Http\Response\HTTPStatus;
use Illuminate\Http\JsonResponse;

/**
 * Class ResponseManager
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Http\Managers
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 29.10.2020
 */
class ResponseManager
{

    /**
     * @var $response object|array
     */
    private $result;

    /**
     * @var boolean $hasResult
     */
    private bool $hasResult = true;

    /** @var HTTPStatus $httpStatus */
    private HTTPStatus $httpStatus;

    /**
     * @return object
     */
    public function getResult(): object
    {
        return $this->result;
    }

    /**
     * @param object|array $result
     */
    public function setResult($result): ResponseManager
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasResult(): bool
    {
        return $this->hasResult;
    }

    /**
     * @param bool $hasResult
     */
    public function setHasResult(bool $hasResult): ResponseManager
    {
        $this->hasResult = $hasResult;

        return $this;
    }

    /**
     * @return HTTPStatus
     */
    public function getHttpStatus(): HTTPStatus
    {
        return $this->httpStatus;
    }

    /**
     * @param HTTPStatus $httpStatus
     * @return ResponseManager
     */
    public function setHttpStatus(HTTPStatus $httpStatus): ResponseManager
    {
        $this->httpStatus = $httpStatus;

        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function build(): JsonResponse
    {
        if(empty($this->result) && $this->hasResult) {
            $httpStatus = new HTTPStatus(204, "No Content");
            $this->setHttpStatus($httpStatus);
        }

        if ($this->httpStatus === null) {
            $httpStatus = new HTTPStatus(444, "Unknown");
            $this->setHttpStatus($httpStatus);
        }

        $response = [
            "success" => true,
            "@method" => \request()->route()[1]['as'],
            "status" => $this->httpStatus->getHttpStatusCode(),
            "statusText" => $this->httpStatus->getHttpStatusText()
        ];

        if ($this->hasResult()) {
            $response['result'] = $this->result;
        }

        return response()->json($response);
    }
}
