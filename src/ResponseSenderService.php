<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use StreamInterop\Interface\ResourceStream;

/**
 * The [_ResponseSenderService_][] affords sending the server response.
 */
interface ResponseSenderService
{
    /**
     * Sends the response.
     *
     * - Directives:
     *
     *     - Implementations MAY check to see if the response can be sent; when
     *       doing so, implementations MUST throw a [_ResponseThrowable_][] if
     *       the response cannot be sent.
     *
     *     - If the [_ResponseStruct_][] `$body` is an instance of
     *       [_ResponseBodyHandler_][], implementations MUST call its
     *       `prepareResponse()` method before sending anything.
     *
     *     - Implementations SHOULD use [`header()`][] to send headers, but MAY
     *       use some other mechanism.
     *
     *     - Implementations SHOULD send header fields in lower case, but MAY
     *       send header fields in some other RFC-approved case.
     *
     * - Notes:
     *
     *     - **Send the body by writing to a stream resource, not by calling
     *       `echo` or `print`.** Although echoing a body string is the single
     *       most common use case, writing to the `php://output` stream does
     *       exactly the same thing. This also allows specifying the output
     *       stream at call-time, such as when testing.
     */
    public function sendResponse(
        ResponseStruct $response,
        ResourceStream $output,
    ) : void;
}
