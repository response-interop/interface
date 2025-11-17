<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use Stringable;

/**
 * The _ResponseStruct_ interface encapsulates the server response.
 */
interface ResponseStruct
{
    /**
     * The status line for the response.
     */
    public ResponseStatusLineStruct $statusLine { get; set; }

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
     *     - If the `$body` is an instance of _ResponseBodyHandler_, implementations
     *       MUST call its `prepareResponse()` method before sending anything.
     *     - Implementations MAY check to see if the response can be sent; when doing
     *       so, implementations MUST throw a _ResponseThrowable_ if the response
     *       cannot be sent.
     */
    public function sendResponse() : void;
}
