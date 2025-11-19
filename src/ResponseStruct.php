<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use StreamInterop\Interface\ResourceStream;
use Stringable;

/**
 * The _ResponseStruct_ interface encapsulates the server response.
 */
interface ResponseStruct
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
     *       _ResponseBodyHandler_ instance affords improved resource management
     *       and response modification.
     */
    public string|Stringable|ResponseBodyHandler $body { get; set; }

    /**
     * Sends the response.
     *
     * - Directives:
     *
     *     - Implementations MAY check to see if the response can be
     *       sent; when doing so, implementations MUST throw a
     *       _ResponseThrowable_ if the response cannot be sent.
     *
     *     - If the `$body` is an instance of _ResponseBodyHandler_,
     *       implementations MUST call its `prepareResponse()` method before
     *       sending anything.
     *
     *     - Implementations SHOULD use `header()` to send headers, but
     *       MAY use some other mechanism.
     *
     *     - Implementations SHOULD send header fields in lower case,
     *       but MAY send header fields in some other RFC-approved case.
     *
     *     - Implementations MUST write the `$body` to the `$stream`.
     *
     * - Notes:
     *
     *     - **Use a stream resource, not `echo`, to send the body.**
     *       Although echoing a body string is the single most common
     *       use case, writing to the `php://output` stream does
     *       exactly the same thing. This also allows specifying the
     *       output stream at call-time, such as when testing.
     */
    public function sendResponse(ResourceStream $stream) : void;
}
