<?php
namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

class Response
{
    /** @var int $statusCode */
    private int $statusCode;

    /** @var mixed $result */
    private $result;

    public function __construct()
    {
        $this->statusCode = -1;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return Response
     */
    public function setStatusCode(int $statusCode): Response
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return Response
     */
    public function setResult($result): Response
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @param string $error
     * @return JsonResponse
     */
    public function buildError(string $error): JsonResponse
    {
        // default status code -> 500
        if ($this->statusCode === -1) {
            $this->statusCode = 500;
        }

        return response()->json([
            "success" => false,
            "error" => $error
        ], $this->statusCode);
    }

    /**
     * @param string $error
     * @return JsonResponse
     */
    public function build(): JsonResponse
    {
        // default status code -> 200
        if ($this->statusCode === -1) {
            $this->statusCode = 200;
        }

        $response = [
            "success" => true,
        ];

        try {
            $response['count'] = count($this->result);
        } catch (\Exception $exception) {}

        if (null !== $this->result) {
            $response['result'] = $this->result;
        }

        return response()->json($response, $this->statusCode);
    }

}