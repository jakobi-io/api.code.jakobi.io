<?php
namespace App\Http\Response;

/**
 * Class HTTPStatus
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Http\Managers
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 29.10.2020
 */
class HTTPStatus
{
    /** @var int $httpStatusCode */
    private int $httpStatusCode;

    /** @var string $httpStatusText */
    private string $httpStatusText;

    /**
     * HTTPStatus constructor.
     *
     * @param int $httpStatusCode
     * @param string $httpStatusText
     */
    public function __construct(int $httpStatusCode, string $httpStatusText)
    {
        $this->httpStatusCode = $httpStatusCode;
        $this->httpStatusText = $httpStatusText;
    }

    /**
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    /**
     * @return string
     */
    public function getHttpStatusText(): string
    {
        return $this->httpStatusText;
    }
}
