<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

/**
 * The _ResponseTypeAliases_ interface defines these PHPStan type aliases to
 * aid static analysis.
 *
 * @phpstan-type response_cookie_array array{
 *     name: response_cookie_name_string,
 *     value: response_header_value_string,
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
 * @phpstan-type response_http_version_string string
 *
 * @phpstan-type response_status_code_int int
 *
 */
interface ResponseTypeAliases
{
}
