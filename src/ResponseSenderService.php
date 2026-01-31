<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use Stringable;

/**
 * [_ResponseSenderService_][] affords sending the response.
 *
 * @phpstan-import-type response_header_field_string from ResponseTypeAliases
 * @phpstan-import-type response_header_value_string from ResponseTypeAliases
 */
interface ResponseSenderService
{
    /**
     * Sends the entire response, including the status line, headers, and body.
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
     *     - Implementations MAY "finish" or "close" the request after sending
     *       the response.
     */
    public function sendResponse(ResponseStruct $response) : void;
}
