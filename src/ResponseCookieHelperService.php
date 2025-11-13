<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

/**
 * Response-Interop affords representing `set-cookie` header values in two
 * ways:
 *
 * - as a `response_header_value_string`, for working with complete `set-cookie`
 *   header strings; and,
 *
 * - as a `response_cookie_array`, for working with `set-cookie` components
 *   more conveniently.
 *
 * The [_ResponseCookieHelperService_][] affords conversion between the two
 * representations.
 *
 * @phpstan-import-type response_cookie_array from ResponseTypeAliases
 * @phpstan-import-type response_header_value_string from ResponseTypeAliases
 */
interface ResponseCookieHelperService
{
    /**
     * Parses a `response_header_value_string` into a `response_cookie_array`.
     *
     * - Directives:
     *     - Implementations MUST use a parsing algorithm equivalent to the one in
     *       [RFC 6265][] section 5.2.
     *
     *     - Implementations MAY ignore the attribute-specific parsing and validating
     *       algorithms in [RFC 6265][] sections 5.2.1 et al.
     *
     *     - Implementations MAY validate parsed attributes; implementations doing so
     *       MUST treat invalid attributes as missing.
     *
     *     - Implementations MUST return `null` when the parsed `<name-value-pair>`
     *       lacks a `%x3D` (`=`) character, or when the parsed cookie name is empty.
     *
     *     - Implementations MUST decode the parsed cookie name and value
     *       appropriately.
     *
     *     - Implementations MUST normalize parsed attribute names to lower case.
     *
     *     - Implementations MUST represent the value of attributes specified without
     *       `=<attribute-value>` as boolean `true`.
     *
     * - Notes:
     *
     *     - **These directives are specific but non-restrictive.** For example,
     *       cookie attributes other than the ones found in [RFC 6265][] may be parsed
     *       and captured into the `response_cookie_array`, such as `SameSite` and
     *       `Partitioned`.
     *
     *     - **The parsed cookie name and value are to be decoded.** Typically
     *       this means using [`urldecode()`][].
     *
     *     - **Some attributes do not have values.** For example, the `HttpOnly`
     *       attribute is defined as having no accompanying value (i.e., it has no
     *       `=<attribute-value>` portion). Thus, if `HttpOnly` is present in the
     *       `response_header_value_string` as an attribute, its corresponding
     *       `response_cookie_array` element must be represented as
     *       `['httponly' => true]`. If it is not present as an attribute, it is
     *       missing, and thus should not be present in the `response_cookie_array`.
     *
     *       Note that this is different from an attribute having an empty value.
     *       For example, `expires=;` has an empty value, and so should be
     *       represented as an empty string: `['expires' => '']`. (This is an
     *       invalid value for `expires` and so implementations may treat it as
     *       missing.)
     *
     * @param response_header_value_string $setCookieString
     * @return ?response_cookie_array
     */
    public function parseResponseCookieString(string $setCookieString) : ?array;

    /**
     * Composes a `response_cookie_array` into a `response_header_value_string`.
     *
     * - Directives:
     *
     *     - Implementations MUST encode the cookie name and value
     *       appropriately.
     *
     *     - Implementations SHOULD use lower case for attribute names but MAY use any
     *       other case approved in the relevant RFCs.
     *
     *     - Implementations MUST omit `=<attribute-value>` when the attribute value
     *       is boolean `true`.
     *
     * - Notes:
     *
     *     - **These directives are specific but non-restrictive.** For example,
     *       cookie attributes other than the ones found in [RFC 6265][] may be
     *       composed into the `response_header_value_string`, such as `SameSite`
     *       and `Partitioned`.
     *
     *     - **The cookie name and value are to be encoded.** Typically this
     *       means using [`urlencode()`][].
     *
     * @param response_cookie_array $cookie
     * @return response_header_value_string
     */
    public function composeResponseCookieString(array $cookie) : string;
}
