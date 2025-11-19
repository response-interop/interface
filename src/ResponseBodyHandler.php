<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use StreamInterop\Interface\ResourceStream;

/**
 * The [_ResponseBodyHandler_][] interface affords management and sending of
 * non-string, resource-intensive, or response-modifying content.
 *
 * - Notes:
 *
 *     - **Not all content is easily managed as an in-memory string.** Although an
 *       in-memory string is the single most common kind of body content, there are
 *       many other kinds of content that a response may represent. The body may be
 *       generated from an array, object, file, stream, or some other source. Many
 *       of these sources might best be converted only as the response is being
 *       sent; for example, when sending a file to download, it may be wise to
 *       send the file in chunks instead ofreading the whole file into memory.
 *
 *     - **Setting and getting content is implementation-specific.** Because of the
 *       varied, domain-specific, and sometimes proprietary requirements of
 *       non-string content, there can be no generic setter or getter interface
 *       here. Implementors are encouraged to publish their implementations for
 *       shared use.
 */
interface ResponseBodyHandler
{
    /**
     * Modifies the `$response` as appropriate for the body content.
     *
     * - Notes:
     *
     *     - **The content source or implementation may carry information relevant
     *       to the rest of the response.** These may include values related to:
     *
     *         - the `content-type` header and its `charset` parameter
     *         - the `content-encoding` header
     *         - an `etag` string
     *         - a `last-modified` time
     *         - the status code
     *         - and so on.
     *
     *       Such information might best be recorded in the response only at the
     *       time of sending. This method affords the opportunity to do so in a
     *       content-specific fashion.
     */
    public function prepareResponse(ResponseStruct $response) : void;

    /**
     * Sends the body content to an output stream.
     *
     * - Directives:
     *
     *     - Implementations MUST write the body content to the `$stream`.
     *
     * - Notes:
     *
     *     - **Sending logic is content- and implementation-specific.** Different
     *       kinds of content require different sending mechanisms. Some kinds may
     *       be amenable to direct output, others may require specific encoding,
     *       and yet others may require more involved resource or stream handling.
     *
     *     - **Use a stream resource, not `echo`, to send the body.**
     *       Although echoing a body string is the single most common
     *       use case, writing to the `php://output` stream does
     *       exactly the same thing. This also allows specifying the
     *       output stream at call-time, such as when testing.
     */
    public function sendResponseBody(ResourceStream $stream) : void;
}
