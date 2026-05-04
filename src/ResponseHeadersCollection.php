<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

/**
 * [_ResponseHeadersCollection_][] encapsulates the headers for the response,
 * including affordances for cookie management.
 *
 * - Directives:
 *
 *     - Implementations MUST normalize each `response_header_field_string`
 *       argument to lower case.
 *
 *     - Implementations MUST validate each `response_header_field_string`
 *       argument, and MUST throw a [_ResponseThrowable_][] on invalidity.
 *
 *     - Implementations MUST throw a [_ResponseThrowable_][] if a
 *       `response_header_value_string` argument is blank.
 *
 *     - Implementations MAY validate other method arguments; when doing
 *       so, implementations MUST throw a [_ResponseThrowable_][] on invalidity.
 *
 * - Notes:
 *
 *     - **Header fields are retained in lower case.** This standardizes
 *       expectations around header field lookups.
 *
 *     - **Header fields must be valid.** In general, this means header fields
 *       should consist only of letters, digits, hyphens (`-`), and underscores
 *       (`_`); the first character should be a letter or an underscore. Cf.
 *       <https://datatracker.ietf.org/doc/html/rfc3864#section-4.1>.
 *
 *     - **Header values cannot be blank.** If `trim($value) === ''` then
 *       the `$value` is blank.
 *
 *     - **Cookies and `set-cookie` headers are kept synchronized.** All
 *       cookies set via `setHeader('set-cookie', ...)`,
 *       `addHeader('set-cookie', ...)`, or `setCookie()` are visible to
 *       all header-access methods: `hasHeader('set-cookie')` and
 *       `hasHeaders()` return `true` whenever any cookies exist, and
 *       `getHeader('set-cookie')` and `getHeaders()` return cookie
 *       values via `getCookiesAsStrings()`.
 *
 *     - **Boolean-flag cookie attributes are specified by presence.**
 *       Attributes that carry no value (e.g. `secure`, `httponly`,
 *       `partitioned`) are represented as boolean `true` when on, and are
 *       absent from the `$attributes` array when off. The type
 *       `response_cookie_attributes_array` does not permit a `false` value.
 *
 * @phpstan-import-type response_cookie_array from ResponseTypeAliases
 * @phpstan-import-type response_cookie_attributes_array from ResponseTypeAliases
 * @phpstan-import-type response_cookie_name_string from ResponseTypeAliases
 * @phpstan-import-type response_cookie_value_string from ResponseTypeAliases
 * @phpstan-import-type response_header_field_string from ResponseTypeAliases
 * @phpstan-import-type response_header_value_string from ResponseTypeAliases
 * @phpstan-import-type response_headers_array from ResponseTypeAliases
 * @phpstan-import-type response_named_cookie_arrays from ResponseTypeAliases
 * @phpstan-import-type response_named_cookie_strings from ResponseTypeAliases
 */
interface ResponseHeadersCollection
{
    /**
     * Sets the `$value` for a header, replacing all existing `$value`s for
     * that header.
     *
     * - Directives:
     *
     *     - If the normalized `$field` is `set-cookie`, implementations MUST
     *       remove all pre-existing cookies, and MUST retain the `$value`
     *       such that the cookie can be retrieved by name (e.g. via
     *       `getCookieAsArray()` or `getCookieAsString()`); if the cookie
     *       cannot be retained in such a way, implementations MUST throw a
     *       [_ResponseThrowable_][].
     *
     * @param response_header_field_string $field
     * @param response_header_value_string $value
     */
    public function setHeader(string $field, string $value) : void;

    /**
     * Adds a `$value` for a header, keeping all previous `$value`s for that
     * header.
     *
     * - Directives:
     *
     *     - If there are no existing `$value`s for the header,
     *       implementations MUST behave as if `setHeader()` was called with
     *       the same `$field` and `$value`.
     *
     *     - Implementations MUST retain each added `$value` separately from
     *       all previous `$value`s.
     *
     *     - If the normalized `$field` is `set-cookie`, implementations MUST
     *       retain the `$value` such that it can be retrieved by name (e.g. via
     *       `getCookieAsArray()` or `getCookieAsString()`); if the cookie
     *       cannot be retained in such a way, implementations MUST throw a
     *       [_ResponseThrowable_][].
     *
     * @param response_header_field_string $field
     * @param response_header_value_string $value
     */
    public function addHeader(string $field, string $value) : void;

    /**
     * Reports if a header exists.
     *
     * - Directives:
     *
     *     - If the normalized `$field` is `set-cookie`, implementations MUST
     *       return `true` when any cookies exist.
     *
     * @param response_header_field_string $field
     */
    public function hasHeader(string $field) : bool;

    /**
     * Returns the `$value`(s) for a header.
     *
     * - Directives:
     *
     *     - Implementations MUST return `null` if there is no `$value` for
     *       the header.
     *
     *     - Implementations MUST return a string if there is only one `$value`
     *       for the header.
     *
     *     - Implementations MUST return an array of strings if there is more
     *       than one `$value` for the header.
     *
     *     - If the normalized `$field` is `set-cookie`, implementations MUST
     *       return cookie values as returned by `getCookiesAsStrings()`.
     *
     * - Notes:
     *
     *     - **This method returns a string if there is only one value.**
     *       This is to support the most common case for most response
     *       headers; i.e., a single value. This reduces the occurrence of
     *       the idiom `getHeader('field-name')[0]`. If consumers require the
     *       return to be an array regardless of the number of values, they
     *       may cast the return to `(array)`.
     *
     *     - **Cookies are always returned as strings.** This is to keep the
     *       return types consistent for all headers, such that the returned
     *       values can be used directly in [`header()`][] calls if needed. In
     *       practical terms, the implementation should use
     *       `getCookiesAsStrings()` as the source for `set-cookie` values.
     *
     * @param response_header_field_string $field
     * @return null|response_header_value_string|response_header_value_string[]
     */
    public function getHeader(string $field) : null|string|array;

    /**
     * Removes a header entirely.
     *
     * - Directives:
     *
     *     - If the normalized `$field` is `set-cookie`, implementations MUST
     *       also remove all cookies.
     *
     * @param response_header_field_string $field
     */
    public function unsetHeader(string $field) : void;

    /**
     * Reports if any headers or cookies exist.
     */
    public function hasHeaders() : bool;

    /**
     * Returns an array of all `$value`s of all headers, keyed by the
     * header field.
     *
     * - Directives:
     *
     *     - Implementations MUST use a string if there is only one `$value`
     *       for a header.
     *
     *     - Implementations MUST use an array of strings if there is more
     *       than one `$value` for a header.
     *
     *     - When any cookies exist, the returned array MUST include a
     *       `set-cookie` key whose value is from `getCookiesAsStrings()`.
     *
     * - Notes:
     *
     *     - **Cookies are always returned as strings.** This is to keep the
     *       return types consistent for all headers, such that the returned
     *       values can be used directly in [`header()`][] calls if needed. In
     *       practical terms, the implementation should use
     *       `getCookiesAsStrings()` as the source for `set-cookie` values.
     *
     * @return response_headers_array
     */
    public function getHeaders() : array;

    /**
     * Removes all headers.
     *
     * - Directives:
     *
     *     - Implementations MUST also remove all cookies.
     */
    public function unsetHeaders() : void;

    /**
     * Sets a named cookie as a `response_cookie_array`, replacing any
     * existing cookie of the same name.
     *
     * - Directives:
     *
     *     - Implementations MUST retain the cookie such that it can be
     *       retrieved by name (e.g. via `getCookieAsArray()` or
     *       `getCookieAsString()`).
     *
     *     - Implementations MUST NOT encode the arguments.
     *
     * @param response_cookie_name_string $name
     * @param response_cookie_value_string $value
     * @param response_cookie_attributes_array $attributes
     */
    public function setCookie(
        string $name,
        string $value,
        array $attributes = [],
    ) : void;

    /**
     * Reports if a named cookie exists.
     *
     * @param response_cookie_name_string $name
     */
    public function hasCookie(string $name) : bool;

    /**
     * Returns a named cookie as a `response_cookie_array`, or `null` if it
     * does not exist.
     *
     * - Directives:
     *
     *     - Implementations retaining the cookie as a
     *       `response_header_value_string` MUST convert it to a
     *       `response_cookie_array` via the [_ResponseCookieHelperService_][]
     *       method `parseResponseCookieString()`.
     *
     * @param response_cookie_name_string $name
     * @return ?response_cookie_array
     */
    public function getCookieAsArray(string $name) : ?array;

    /**
     * Returns a named cookie as a string suitable for use as a header
     * value, or `null` if it does not exist.
     *
     * - Directives:
     *
     *     - Implementations retaining the cookie as a
     *       `response_cookie_array` MUST convert it to a
     *       `response_header_value_string` via the
     *       [_ResponseCookieHelperService_][] method
     *       `composeResponseCookieString()`.
     *
     * @param response_cookie_name_string $name
     * @return ?response_header_value_string
     */
    public function getCookieAsString(string $name) : ?string;

    /**
     * Removes a named cookie.
     *
     * - Notes:
     *
     *     - **This is not the same as deleting a cookie from the browser.**
     *       To do that, consumers need to send a named cookie with an
     *       expiration date in the past.
     *
     * @param response_cookie_name_string $name
     */
    public function unsetCookie(string $name) : void;

    /**
     * Reports if any cookies exist.
     */
    public function hasCookies() : bool;

    /**
     * Returns all cookies as an array where each key is the cookie name
     * and each value is its `response_cookie_array`.
     *
     * - Directives:
     *
     *     - Implementations retaining a cookie as a
     *       `response_header_value_string` MUST represent that cookie as if
     *       it had been retrieved via the `getCookieAsArray()` method.
     *
     * @return response_named_cookie_arrays
     */
    public function getCookiesAsArrays() : array;

    /**
     * Returns all cookies as an array where each key is the cookie name
     * and each value is its `response_header_value_string`.
     *
     * - Directives:
     *
     *     - Implementations retaining a cookie as a `response_cookie_array`
     *       MUST represent that cookie as if it had been retrieved via the
     *       `getCookieAsString()` method.
     *
     * @return response_named_cookie_strings
     */
    public function getCookiesAsStrings() : array;

    /**
     * Removes all cookies.
     *
     * - Directives:
     *
     *     - Implementations MUST behave as if `unsetHeader('set-cookie')`
     *       had been called.
     *
     * - Notes:
     *
     *     - **This is not the same as deleting all cookies from the
     *       browser.** To do that, consumers need to send named cookies with
     *       expiration dates in the past.
     */
    public function unsetCookies() : void;
}
