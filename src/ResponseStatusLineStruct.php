<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

/**
 * The _ResponseStatusLineStruct_ interface encapsulates the status line
 * for the server response.
 *
 * @phpstan-import-type response_http_version_string from ResponseTypeAliases
 *
 * @phpstan-import-type response_status_code_int from ResponseTypeAliases
 */
interface ResponseStatusLineStruct
{
    /**
     * The HTTP version string for the response; e.g. `'1.1'`.
     *
     * - Directives:
     *
     *     - Implementations MAY validate this value; implementations doing
     *       so MUST throw a _ResponseThrowable_ on invalidity.
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
     *       so MUST throw a _ResponseThrowable_ on invalidity.
     *
     * @var response_status_code_int
     */
    public int $statusCode { get; set; }

    /**
     * Sends the response status line.
     */
    public function sendResponseStatusLine() : void;
}
