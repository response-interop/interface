<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

/**
 * [_ResponseTypeAliases_][] provides PHPStan type aliases to aid static
 * analysis.
 *
 * - ```
 *   response_cookie_array array{
 *       name: response_cookie_name_string,
 *       value: response_cookie_value_string,
 *       attributes: response_cookie_attributes_array
 *   }
 *   ```
 *     - An `array` of cookie components.
 *
 * - ```
 *   response_cookie_attributes_array array{
 *       expires?:string,
 *       max-age?:numeric-string,
 *       path?:string,
 *       domain?:string,
 *       secure?:true,
 *       httponly?:true,
 *       samesite?:string,
 *       partitioned?:true,
 *       ...<string, string|true>
 *   }
 *   ```
 *     - An `array` intended to specify cookie attributes.
 *
 * - `response_cookie_name_string`
 *     - A `string` intended as a cookie name.
 *
 * - `response_cookie_value_string`
 *     - A `string` intended as a cookie value.
 *
 * - `response_header_field_string`
 *     - A `string` intended to be a header field name, typically as part of the
 *       first argument to [`header()`][].
 *
 * - `response_header_value_string`
 *     - A `string` intended to be header value, typically as part of the first
 *       argument to [`header()`][].
 *
 * - ```
 *   response_headers_array array<
 *       response_header_field_string,
 *       response_header_value_string|response_header_value_string[]
 *   >
 *   ```
 *     - An `array` of header values keyed on the header fields.
 *
 * - `response_http_version_string`
 *     - A `string` used for specifying an HTTP version.
 *
 * - ```
 *   response_named_cookie_arrays array<
 *       response_cookie_name_string,
 *       response_cookie_array
 *   >
 *   ```
 *     - An `array` of cookie component arrays keyed on the cookie name.
 *
 * - ```
 *   response_named_cookie_strings array<
 *       response_cookie_name_string,
 *       response_header_value_string
 *   >
 *   ```
 *     - An `array` of cookie header strings keyed on the cookie name.
 *
 * - `response_status_code_int`
 *     - An `int` specifying an HTTP response code.
 *
 * @phpstan-type response_cookie_array array{
 *     name: response_cookie_name_string,
 *     value: response_cookie_value_string,
 *     attributes: response_cookie_attributes_array
 * }
 *
 * @phpstan-type response_cookie_attributes_array array{
 *     expires?:string,
 *     max-age?:numeric-string,
 *     path?:string,
 *     domain?:string,
 *     secure?:true,
 *     httponly?:true,
 *     samesite?:string,
 *     partitioned?:true,
 *     ...<string, string|true>
 * }
 *
 * @phpstan-type response_cookie_name_string string
 *
 * @phpstan-type response_cookie_value_string string
 *
 * @phpstan-type response_header_field_string string
 *
 * @phpstan-type response_header_value_string string
 *
 * @phpstan-type response_headers_array array<
 *     response_header_field_string,
 *     response_header_value_string|response_header_value_string[]
 * >
 *
 * @phpstan-type response_http_version_string string
 *
 * @phpstan-type response_named_cookie_arrays array<
 *     response_cookie_name_string,
 *     response_cookie_array
 * >
 *
 * @phpstan-type response_named_cookie_strings array<
 *     response_cookie_name_string,
 *     response_header_value_string
 * >
 *
 * @phpstan-type response_status_code_int int
 *
 */
interface ResponseTypeAliases
{
}
