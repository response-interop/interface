<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use Stringable;

/**
 * The [_ResponseStruct_][] interface encapsulates the server response.
 *
 * @phpstan-import-type response_http_version_string from ResponseTypeAliases
 *
 * @phpstan-import-type response_status_code_int from ResponseTypeAliases
 */
interface ResponseStruct
{
    /**
     * The HTTP version string for the response; e.g. `'1.1'`.
     *
     * - Directives:
     *
     *     - Implementations MAY validate this value; implementations doing
     *       so MUST throw a [_ResponseThrowable_][] on invalidity.
     *
     * @var response_http_version_string
     */
    public string $httpVersion { get; set; }

    /**
     * The status code for the response; e.g. `200`.
     *
     * - Directives:
     *
     *     - Implementations MAY validate this value; implementations doing
     *       so MUST throw a [_ResponseThrowable_][] on invalidity.
     *
     * @var response_status_code_int
     */
    public int $statusCode { get; set; }

    /**
     * The headers for the response, including affordances for cookie management.
     */
    public ResponseHeadersCollection $headers { get; set; }

    /**
     * The body for the response.
     *
     * - Notes:
     *
     *     - **The `$body` may be a string, a _Stringable_ object, or some other
     *       content source.** The single most common kind of body content is an
     *       in-memory string. However, there are other common kinds of content,
     *       such as when sending a large file for download, at which point a
     *       [_ResponseBodyHandler_][] instance affords improved resource management
     *       and response modification.
     */
    public string|Stringable|ResponseBodyHandler $body { get; set; }
}
