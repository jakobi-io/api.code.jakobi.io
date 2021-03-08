<?php
namespace App\Http\Response;

use Illuminate\Http\JsonResponse;

class Response extends \Illuminate\Http\Response
{

    /** @var mixed $result */
    private $result;

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
    public function error(string $error): JsonResponse
    {
        return response()->json([
            "success" => false,
            "statusCode" => $this->statusCode,
            "statusText" => $this->statusText,
            "method" => \request()->route()[1]['as'] ?? "error",
            "error" => $error
        ], $this->statusCode);
    }

    /**
     * @param string $rror
     * @return JsonResponse
     */
    public function build(): JsonResponse
    {
        $response = [
            "success" => true,
            "statusCode" => $this->statusCode,
            "statusText" => $this->statusText,
            "method" => \request()->route()[1]['as'] ?? "error"
        ];

        try {
            $response['count'] = count($this->result);
        } catch (\Exception $exception) {}

        $response['result'] = $this->result;

        return response()->json($response, $this->statusCode);
    }

}