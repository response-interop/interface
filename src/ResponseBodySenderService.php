<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use Stringable;

/**
 * [_ResponseBodySenderService_][] affords sending the response body.
 *
 */
interface ResponseBodySenderService
{
    /**
     * Sends body content from a string.
     *
     * - Directives:
     *
     *     - Implementations SHOULD write the string to the `php://output`
     *       stream, but MAY use some other mechanism or destination.
     *
     * - Notes:
     *
     *     - **Prefer writing to a resource over calling `echo`, `print`,
     *       etc.** Although echoing a body string is the single most common
     *       use case, calling `fwrite()` with a `php://output` resource does
     *       exactly the same thing. This also allows specifying the output
     *       destination at call-time, such as when testing.
     */
    public function sendResponseBodyString(string|Stringable $content) : void;

    /**
     * Sends body content from a resource.
     *
     * - Directives:
     *
     *     - Implementations SHOULD send the `$content` to the `php://output`
     *       stream, but MAY use some other mechanism or destination.
     *
     *     - If the `$offset` is `null`, implementations MUST begin reading
     *       from the current `$content` pointer position.
     *
     *     - If the `$offset` is zero or positive, implementations MUST begin
     *       reading from the `$content` starting at that byte; implementations
     *       MAY move the pointer as needed, e.g. via [`fseek()`][].
     *
     *     - If the `$length` is `null`, implementations MUST send all remaining
     *       bytes from the `$content`.
     *
     *     - If the `$length` is not `null`, implementations MUST send that many
     *       bytes from the `$content` (or all remaining bytes from the `$content`,
     *       whichever comes first).
     *
     *     - Implementations MUST return the number of bytes sent.
     *
     *     - Implementations MUST throw a [_ResponseThrowable_][] on failure.
     *
     * - Notes:
     *
     *     - **The method signature is subtly different from related streaming
     *       functions in PHP.** Whereas [`stream_copy_to_stream()`][] defaults
     *       to `$offset = 0`, and [`stream_get_contents()`][] defaults to
     *       `-1`, the default here is `null`.
     *
     *     - **By default, do not move the starting pointer position.** Some
     *       implementations attempt to [`rewind()`][] the resource before
     *       sending. When sending a complete file, that may be fine; however,
     *       it may be necessary to start at exactly where the resource pointer
     *       already is. Therefore, do not change the pointer starting position
     *       when the `$offset` is `null`.
     *
     *     - **An `$offset` of `0` is the equivalent of rewind-before-send.**
     *       To indicate a [`rewind()`][] or its equivalent is needed before
     *       sending, consumers should specify an `$offset` of `0`.
     *       Alternatively, consumers might [`rewind()`][] the resource
     *       themselves before sending.
     *
     * @param resource $content
     */
    public function sendResponseBodyResource(
        mixed $content,
        ?int $length = null,
        ?int $offset = null,
    ) : int;

    /**
     * Flushes the system output buffer.
     *
     * - Notes:
     *
     *     - **This is an equivalent to [`flush()`][].** It may be useful when
     *       sending content with `Transfer-Encoding: chunked`.
     */
    public function flushResponse() : void;
}
