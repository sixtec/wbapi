<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Exceptions;

use GuzzleHttp\Exception\RequestException;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class HttpException extends WBMetaException
{
    public static function fromRequestException(RequestException $e): self
    {
        return new self(
            message: $e->getMessage(),
            code: $e->getCode(),
            previous: $e,
        );
    }

    public static function unexpectedResponse(int $statusCode, string $body): self
    {
        return new self("Unexpected API response [{$statusCode}]: {$body}");
    }
}
