# Response-Interop Interface Package

Response-Interop provides an interoperable package of standard interfaces to
encapsulate, buffer, and send server-side response values in PHP 8.4 or later,
in order to reduce the global mutable state and inspection problems that exist
with the PHP response-sending functions. It reflects, refines, and reconciles
the common practices identified within [several pre-existing projects](./README-RESEARCH.md).

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL" in this document are to be
interpreted as described in [BCP 14][] ([RFC 2119][], [RFC 8174][]).

## Interfaces

This package defines the following interfaces:

- [_ResponseStruct_][] encapsulates the server response.

- [_ResponseHeadersCollection_][] encapsulates the headers for the response, including affordances for cookie management.

- [_ResponseBodyHandler_][] affords management of non-string, resource-intensive, or header-modifying content.

- [_ResponseCookieHelperService_][] affords representing `set-cookie` header values.

- [_ResponseSenderService_][] affords sending the response.

- [_ResponseBodySenderService_][] affords sending the response body.

- [_ResponseThrowable_][] extends [_Throwable_][] to mark an [_Exception_][] as response-related.

- [_ResponseTypeAliases_][] provides PHPStan type aliases to aid static analysis.

Notes:

- **Response-Interop interfaces are mutable.** None of the researched projects
  afforded readonly or immutable response objects.

- **Whereas PHP sends-as-it-goes, Response-Interop collects-then-sends.**
  For example, PHP sends a header at the moment the [`header()`][] function
  is called. In contrast, Response-Interop buffers the header specifications,
  and sends them only when the `sendResponse()` method is called.

### _ResponseStruct_

[_ResponseStruct_][] encapsulates the server response.

#### _ResponseStruct_ Properties

- ```php
  public response_http_version_string $httpVersion { get; set; }
  ```
    - The HTTP version string for the response; e.g. `'1.1'`.

    - Directives:

        - Implementations MAY validate this value; implementations doing
          so MUST throw a [_ResponseThrowable_][] on invalidity.

- ```php
  public response_status_code_int $statusCode { get; set; }
  ```
    - The status code for the response; e.g. `200`.

    - Directives:

        - Implementations MAY validate this value; implementations doing
          so MUST throw a [_ResponseThrowable_][] on invalidity.

- ```php
  public ResponseHeadersCollection $headers { get; set; }
  ```
    - The headers for the response, including affordances for cookie management.

- ```php
  public Stringable|ResponseBodyHandler|string $body { get; set; }
  ```
    - The body for the response.

    - Notes:

        - **The `$body` may be a string, a _Stringable_ object, or some other
          content source.** The single most common kind of body content is an
          in-memory string. However, there are other common kinds of content,
          such as when sending a large file for download, at which point a
          [_ResponseBodyHandler_][] instance affords improved resource management
          and response modification.

### _ResponseHeadersCollection_

[_ResponseHeadersCollection_][] encapsulates the headers for the response,
including affordances for cookie management.

- Directives:

    - Implementations MUST normalize each `response_header_field_string`
      argument to lower case.

    - Implementations MUST validate each `response_header_field_string`
      argument, and MUST throw a [_ResponseThrowable_][] on invalidity.

    - Implementations MUST throw a [_ResponseThrowable_][] if a
      `response_header_value_string` argument is blank.

    - Implementations MAY validate other method arguments; when doing
      so, implementations MUST throw a [_ResponseThrowable_][] on invalidity.

- Notes:

    - **Header fields are retained in lower case.** This standardizes
      expectations around header field lookups.

    - **Header fields must be valid.** In general, this means header fields
      should consist only of letters, digits, hyphens (`-`), and underscores
      (`_`); the first character should be a letter or an underscore. Cf.
      <https://datatracker.ietf.org/doc/html/rfc3864#section-4.1>.

    - **Header values cannot be blank.** If `trim($value) === ''` then
      the `$value` is blank.

#### _ResponseHeadersCollection_ Methods

- ```php
  public function setHeader(
      response_header_field_string $field,
      response_header_value_string $value,
  ) : void;
  ```
    - Sets the `$value` for a header, replacing all existing `$value`s for
    that header.

    - Directives:

        - If the normalized `$field` is `set-cookie`, implementations MUST
          retain the `$value` such that the cookie can be retrieved by
          name (e.g. via `getCookieAsArray()` or `getCookieAsString()`);
          if the cookie cannot be retained in such a way, implementations
          MUST throw a [_ResponseThrowable_][].

- ```php
  public function addHeader(
      response_header_field_string $field,
      response_header_value_string $value,
  ) : void;
  ```
    - Adds a `$value` for a header, keeping all previous `$value`s for that
    header.

    - Directives:

        - If there are no existing `$value`s for the header,
          implementations MUST behave as if `setHeader()` was called with
          the same `$field` and `$value`.

        - Implementations MUST retain each added `$value` separately from
          all previous `$value`s.

        - If the normalized `$field` is `set-cookie`, implementations MUST
          retain the `$value` such that it can be retrieved by name (e.g. via
          `getCookieAsArray()` or `getCookieAsString()`); if the cookie
          cannot be retained in such a way, implementations MUST throw a
          [_ResponseThrowable_][].

- ```php
  public function hasHeader(response_header_field_string $field) : bool;
  ```
    - Reports if a header exists.

- ```php
  public function getHeader(
      string $field,
  ) : null|response_header_value_string|response_header_value_string[];
  ```
    - Returns the `$value`(s) for a header.

    - Directives:

        - Implementations MUST return `null` if there is no `$value` for
          the header.

        - Implementations MUST return a string if there is only one `$value`
          for the header.

        - Implementations MUST return an array of strings if there is more
          than one `$value` for the header.

    - Notes:

        - **This method returns a string if there is only one value.**
          This is to support the most common case for most response
          headers; i.e., a single value. This reduces the occurrence of
          the idiom `getHeader('field-name')[0]`. If consumers require the
          return to be an array regardless of the number of values, they
          may cast the return to `(array)`.

        - **Cookies are always returned as strings.** This is to keep the
          return types consistent for all headers, such that the returned
          values can be used directly in [`header()`][] calls if needed. In
          practical terms, the implementation should use
          `getCookiesAsStrings()` as the source for `set-cookie` values.

- ```php
  public function unsetHeader(response_header_field_string $field) : void;
  ```
    - Removes a header entirely.

- ```php
  public function hasHeaders() : bool;
  ```
    - Reports if any headers exist.

- ```php
  public function getHeaders() : response_headers_array;
  ```
    - Returns an array of all `$value`s of all headers, keyed by the
    header field.

    - Directives:

        - Implementations MUST use a string if there is only one `$value`
          for a header.

        - Implementations MUST use an array of strings if there is more
          than one `$value` for a header.

    - Notes:

        - **Cookies are always returned as strings.** This is to keep the
          return types consistent for all headers, such that the returned
          values can be used directly in [`header()`][] calls if needed. In
          practical terms, the implementation should use
          `getCookiesAsStrings()` as the source for `set-cookie` values.

- ```php
  public function unsetHeaders() : void;
  ```
    - Removes all headers.

- ```php
  public function setCookie(
      response_cookie_name_string $name,
      response_cookie_value_string $value,
      response_cookie_attributes_array $attributes = [],
  ) : void;
  ```
    - Sets a named cookie as a `response_cookie_array`, replacing any
    existing cookie of the same name.

    - Directives:

        - Implementations MUST retain the cookie such that it can be
          retrieved by name (e.g. via `getCookieAsArray()` or
          `getCookieAsString()`).

        - Implementations MUST NOT encode the arguments.

- ```php
  public function hasCookie(response_cookie_name_string $name) : bool;
  ```
    - Reports if a named cookie exists.

- ```php
  public function getCookieAsArray(
      response_cookie_name_string $name,
  ) : ?response_cookie_array;
  ```
    - Returns a named cookie as a `response_cookie_array`, or `null` if it
    does not exist.

    - Directives:

        - Implementations retaining the cookie as a
          `response_header_value_string` MUST convert it to a
          `response_cookie_array` via the [_ResponseCookieHelperService_][]
          method `parseResponseCookieString()`.

- ```php
  public function getCookieAsString(
      response_cookie_name_string $name,
  ) : ?response_header_value_string;
  ```
    - Returns a named cookie as a string suitable for use as a header
    value, or `null` if it does not exist.

    - Directives:

        - Implementations retaining the cookie as a
          `response_cookie_array` MUST convert it to a
          `response_header_value_string` via the
          [_ResponseCookieHelperService_][] method
          `composeResponseCookieString()`.

- ```php
  public function unsetCookie(response_cookie_name_string $name) : void;
  ```
    - Removes a named cookie.

    - Notes:

        - **This is not the same as deleting a cookie from the browser.**
          To do that, consumers need to send a named cookie with an
          expiration date in the past.

- ```php
  public function hasCookies() : bool;
  ```
    - Reports if any cookies exist.

- ```php
  public function getCookiesAsArrays() : response_named_cookie_arrays;
  ```
    - Returns all cookies as an array where each key is the cookie name
    and each value is its `response_cookie_array`.

    - Directives:

        - Implementations retaining a cookie as a
          `response_header_value_string` MUST represent that cookie as if
          it had been retrieved via the `getCookieAsArray()` method.

- ```php
  public function getCookiesAsStrings() : response_named_cookie_strings;
  ```
    - Returns all cookies as an array where each key is the cookie name
    and each value is its `response_header_value_string`.

    - Directives:

        - Implementations retaining a cookie as a `response_cookie_array`
          MUST represent that cookie as if it had been retrieved via the
          `getCookieAsString()` method.

- ```php
  public function unsetCookies() : void;
  ```
    - Removes the `set-cookie` header entirely.

    - Notes:

        - **This is not the same as deleting all cookies from the
          browser.** To do that, consumers need to send named cookies with
          expiration dates in the past.

### _ResponseBodyHandler_

[_ResponseBodyHandler_][] affords management of non-string,
resource-intensive, or header-modifying content.

- Notes:

    - **Not all content is easily managed as an in-memory string.** Although an
      in-memory string is the single most common kind of body content, there are
      many other kinds of content that a response may represent. The body may be
      generated from an array, object, file, stream, or some other source. Many
      of these sources might best be converted only as the response is being
      sent; for example, when sending a file to download, it may be wise to
      send the file in chunks instead of reading the whole file into memory.

    - **Setting and getting content is implementation-specific.** Because of the
      varied, domain-specific, and sometimes proprietary requirements of
      non-string content, there can be no generic setter or getter interface
      here. Implementors are encouraged to publish their implementations for
      shared use.

#### _ResponseBodyHandler_ Methods

- ```php
  public function prepareResponse(ResponseStruct $response) : void;
  ```
    - Modifies the `$response` as appropriate for the body content.

    - Notes:

        - **The content source or implementation may carry information relevant
          to the rest of the response.** These may include values related to:

            - the `content-type` header and its `charset` parameter
            - the `content-encoding` header
            - an `etag` string
            - a `last-modified` time
            - the status code
            - and so on.

          Such information might best be recorded in the response only at the
          time of sending. This method affords the opportunity to do so in a
          content-specific fashion.

- ```php
  public function sendResponseBody(ResponseBodySenderService $bodySender) : void;
  ```
    - Sends the body content of the response.

    - Directives:

        - Implementations MUST send the body content using the `$sender`.

    - Notes:

        - **Send the body via the `$sender`, not by using `echo` or some
          other means.** This allows the sending logic to specify the output
          destination. The `$sender` provides affordances for sending strings
          and resources (whether in whole or in part).

### _ResponseCookieHelperService_

[_ResponseCookieHelperService_][] affords representing `set-cookie` header
values.

It does so in two ways, allowing conversion between two representations:

- as a `response_header_value_string`, for working with complete `set-cookie`
  header strings; and,

- as a `response_cookie_array`, for working with `set-cookie` components
  more conveniently.

#### _ResponseCookieHelperService_ Methods

- ```php
  public function parseResponseCookieString(
      response_header_value_string $setCookieString,
  ) : ?response_cookie_array;
  ```
    - Parses a `response_header_value_string` into a `response_cookie_array`.

    - Directives:
        - Implementations MUST use a parsing algorithm equivalent to the one in
          [RFC 6265][] section 5.2.

        - Implementations MAY ignore the attribute-specific parsing and validating
          algorithms in [RFC 6265][] sections 5.2.1 et al.

        - Implementations MAY validate parsed attributes; implementations doing so
          MUST treat invalid attributes as missing.

        - Implementations MUST return `null` when the parsed `<name-value-pair>`
          lacks a `%x3D` (`=`) character, or when the parsed cookie name is empty.

        - Implementations MUST decode the parsed cookie name and value
          appropriately.

        - Implementations MUST normalize parsed attribute names to lower case.

        - Implementations MUST represent the value of attributes specified without
          `=<attribute-value>` as boolean `true`.

    - Notes:

        - **These directives are specific but non-restrictive.** For example,
          cookie attributes other than the ones found in [RFC 6265][] may be parsed
          and captured into the `response_cookie_array`, such as `SameSite` and
          `Partitioned`.

        - **The parsed cookie name and value are to be decoded.** Typically
          this means using [`urldecode()`][].

        - **Some attributes do not have values.** For example, the `HttpOnly`
          attribute is defined as having no accompanying value (i.e., it has no
          `=<attribute-value>` portion). Thus, if `HttpOnly` is present in the
          `response_header_value_string` as an attribute, its corresponding
          `response_cookie_array` element must be represented as
          `['httponly' => true]`. If it is not present as an attribute, it is
          missing, and thus should not be present in the `response_cookie_array`.

          Note that this is different from an attribute having an empty value.
          For example, `expires=;` has an empty value, and so should be
          represented as an empty string: `['expires' => '']`. (This is an
          invalid value for `expires` and so implementations may treat it as
          missing.)

- ```php
  public function composeResponseCookieString(
      response_cookie_array $cookie,
  ) : response_header_value_string;
  ```
    - Composes a `response_cookie_array` into a `response_header_value_string`.

    - Directives:

        - Implementations MUST encode the cookie name and value
          appropriately.

        - Implementations SHOULD use lower case for attribute names but MAY use any
          other case approved in the relevant RFCs.

        - Implementations MUST omit `=<attribute-value>` when the attribute value
          is boolean `true`.

    - Notes:

        - **These directives are specific but non-restrictive.** For example,
          cookie attributes other than the ones found in [RFC 6265][] may be
          composed into the `response_header_value_string`, such as `SameSite`
          and `Partitioned`.

        - **The cookie name and value are to be encoded.** Typically this
          means using [`urlencode()`][].

### _ResponseSenderService_

[_ResponseSenderService_][] affords sending the response.

#### _ResponseSenderService_ Methods

- ```php
  public function sendResponse(ResponseStruct $response) : void;
  ```
    - Sends the entire response, including the status line, headers, and body.

    - Directives:

        - Implementations MAY check to see if the response can be sent; when
          doing so, implementations MUST throw a [_ResponseThrowable_][] if
          the response cannot be sent.

        - If the [_ResponseStruct_][] `$body` is an instance of
          [_ResponseBodyHandler_][], implementations MUST call its
          `prepareResponse()` method before sending anything.

        - Implementations SHOULD use [`header()`][] to send headers, but MAY
          use some other mechanism.

        - Implementations SHOULD send header fields in lower case, but MAY
          send header fields in some other RFC-approved case.

        - Implementations MAY "finish" or "close" the request after sending
          the response.

### _ResponseBodySenderService_

[_ResponseBodySenderService_][] affords sending the response body.

#### _ResponseBodySenderService_ Methods

- ```php
  public function sendResponseBodyString(Stringable|string $content) : void;
  ```
    - Sends body content from a string.

    - Directives:

        - Implementations SHOULD write the string to the `php://output`
          stream, but MAY use some other mechanism or destination.

    - Notes:

        - **Prefer writing to a resource over calling `echo`, `print`,
          etc.** Although echoing a body string is the single most common
          use case, calling `fwrite()` with a `php://output` resource does
          exactly the same thing. This also allows specifying the output
          destination at call-time, such as when testing.

- ```php
  public function sendResponseBodyResource(
      resource $content,
      ?int $length = null,
      ?int $offset = null,
  ) : int;
  ```
    - Sends body content from a resource.

    - Directives:

        - Implementations SHOULD send the `$content` to the `php://output`
          stream, but MAY use some other mechanism or destination.

        - If the `$offset` is `null`, implementations MUST begin reading
          from the current `$content` pointer position.

        - If the `$offset` is zero or positive, implementations MUST begin
          reading from the `$content` starting at that byte; implementations
          MAY move the pointer as needed, e.g. via [`fseek()`][].

        - If the `$length` is `null`, implementations MUST send all remaining
          bytes from the `$content`.

        - If the `$length` is not `null`, implementations MUST send that many
          bytes from the `$content` (or all remaining bytes from the `$content`,
          whichever comes first).

        - Implementations MUST return the number of bytes sent.

        - Implementations MUST throw a [_ResponseThrowable_][] on failure.

    - Notes:

        - **The method signature is subtly different from related streaming
          functions in PHP.** Whereas [`stream_copy_to_stream()`][] defaults
          to `$offset = 0`, and [`stream_get_contents()`] defaults to
          `-1`, the default here is `null`.

        - **By default, do not move the starting pointer position.** Some
          implementations attempt to [`rewind()`][] the resource before
          sending. When sending a complete file, that may be fine; however,
          it may be necessary to start at exactly where the resource pointer
          already is. Therefore, do not change the pointer starting position
          when the `$offset` is `null`.

        - **An `$offset` of `0` is the equivalent of rewind-before-send.**
          To indicate a [`rewind()`][] or its equivalent is needed before
          sending, consumers should specify an `$offset` of `0`.
          Alternatively, consumers might [`rewind()`][] the resource
          themselves before sending.

- ```php
  public function flushResponse() : void;
  ```
    - Flushes the system output buffer.

    - Notes:

        - **This is an equivalent to [`flush()`][].** It may be useful when
          sending content with `Transfer-Encoding: chunked`.

### _ResponseThrowable_

[_ResponseThrowable_][] extends [_Throwable_][] to mark an [_Exception_][] as
response-related.

It adds no class members.

### _ResponseTypeAliases_

[_ResponseTypeAliases_][] provides PHPStan type aliases to aid static
analysis.

- ```
  response_cookie_array array{
      name: response_cookie_name_string,
      value: response_cookie_value_string,
      attributes: response_cookie_attributes_array
  }
  ```
    - An `array` of cookie components.

- ```
  response_cookie_attributes_array array{
      expires?:string,
      max-age?:numeric-string,
      path?:string,
      domain?:string,
      secure?:true,
      httponly?:true,
      samesite?:string,
      partitioned?:true,
  }
  ```
    - An `array` intended to specify cookie attributes.

- `response_cookie_name_string`
    - A `string` intended as a cookie name.

- `response_cookie_value_string`
    - A `string` intended as a cookie value.

- `response_header_field_string`
    - A `string` intended to be a header field name, typically as part of the
      first argument to [`header()`][].

- `response_header_value_string`
    - A `string` intended to be header value, typically as part of the first
      argument to [`header()`][].

- ```
  response_headers_array array<
      response_header_field_string,
      response_header_value_string|response_header_value_string[]
  >
  ```
    - An `array` of header values keyed on the header fields.

- `response_http_version_string`
    - A `string` used for specifying an HTTP version.

- ```
  response_named_cookie_arrays array<
      response_cookie_name_string,
      response_cookie_array
  >
  ```
    - An `array` of cookie component arrays keyed on the cookie name.

- ```
  response_named_cookie_strings array<
      response_cookie_name_string,
      response_header_value_string
  >
  ```
    - An `array` of cookie header strings keyed on the cookie name.

- `response_status_code_int`
    - An `int` specifying an HTTP response code.

## Implementations

- Directives:

    - Implementations MAY define additional class members not defined in these
      interfaces.

- Notes:

    - **Reference implementations** may be found at <https://github.com/response-interop/impl>.

## Q & A

### Why are there only mutable (as vs. immutable or readonly) interfaces?

None of the researched projects model their response objects as immutable or
readonly.

### Why is it a _Response*Struct*_ and not just a _Response_ ?

Response-Interop wants to avoid _Interface_ suffixes, and wants to avoid making
implementors use import aliases. Calling it a _Response_ would mean any
implementation also called _Response_ would have to alias the interop interface.
It is the difference between this less-preferable alternative ...

```php
use ResponseInterop\Interface\Response as ResponseInteropInterface;

class Response implements ResponseInteropInterface
{
    // ...
}
```

... and this more-preferable one:

```php
use ResponseInterop\Interface\ResponseStruct;

class Response implements ResponseStruct
{
    // ...
}
```

Further, the _Response_ definition is struct-like in that it is composed almost entirely of properties.

It is true that none of the researched implementations use _Struct_ in their naming; but then, the interop is for the interface, so existing implementation names can remain as they are.

### Why is _ResponseStruct_ not identical to a client-side response interface?

None of the researched projects model their response objects that way.

A more general answer is from Fowler in _Patterns of Enterprise Application Architecture_
(2003, p 21):

> ... I think there is a good distinction to be made between an interface that
> you provide as a service to others and your use of someone else's service.
> ... I find it beneficial to think about these differently because the
> difference in clients alters the way you think about the service.

Response-Interop attempts to model an interface that *provides* a response for
presentation, not one that *uses* a response from an external source.

### Why does _ResponseStruct_ not provide constants for status codes?

Of the 13 researched projects:

- 2 provide a constant or Enum for reason-phrase mappings to status codes; and,
- 2 others provide a static array of status codes mappings to reason phrases.

The relative rarity, and inconsistency, of such constants and mappings makes it
difficult to discern a standard here.

Implementors desiring status codes constants or Enums are not prevented from
providing them with their implementations.

### Why does _ResponseStruct_ not have a property for the reason phrase?

6 of the 13 projects allow for a reason phrase in one way or another. Thus,
while not the majority design choice, allowing for a reason phrase warrants
consideration.

On further inspection, some of the projects that allow for a reason phrase set
it with the status code, while others set it separately. This makes it
difficult to resolve the differences between the projects. Given that the HTTP
specifications indicate reason phrases are optional, Response-Interop does not
attempt to resolve those differences.

Implementors desiring a reason phrase are encouraged to add one appropriate for
the status code, perhaps in their `sendResponse()` logic.

### Why is _ResponseStruct_ not self-sending?

The majority of researched projects (9/13) have a `send()` method, or its
equivalent, directly on their response objects. The remainder place the sending
logic somewhere outside the response object itself.

Earlier versions of Response-Interop defined a _ResponseStruct_ method called
`sendResponse()` for sending the response. However, private review suggested
that a struct-like object ought not to have methods on it.

Further, private review indicated that separating the sending logic to its own
interface would make it easier to provide alternative mechanisms for sending,
such as during testing, or when integrating with pre-existing packages and
libraries.

With all that in mind, Response-Interop opts to separate the sending logic to
its own interface, _ResponseSenderService_. This keeps the _ResponseStruct_
more struct-like, and makes the sending logic independent of any particular
_ResponseStruct_ implementation.

### Why not put the _ResponseHeadersCollection_ methods directly on the _ResponseStruct_?

Research revealed that separate header and/or cookie collections are used in 6
of the 13 projects. Thus, while not the majority design choice, delegating these
methods to a separate object is common enough to warrant consideration.

With that in mind, Response-Interop finds that the segregation of HTTP version,
status code, headers, and body into their own properties appropriately separates
the concerns around building a response.

### Why does _ResponseHeadersCollection_ allow `string` but not _Stringable_ `$value` types?

None of the researched projects do so. Further, doing so adds complexity to the
implementation directives on how to retain such values, as well as to the return
typehints on the various getter methods. Avoiding _Stringable_ therefore reduces
the implementation burden.

If consumers need to pass a _Stringable_, they may cast it to `(string)` at
call-time.

### Why does _ResponseHeadersCollection_ return individual headers *either* as `string` *or* as `array`?

Although some response headers may hold multiple values, the main use-case for
most response headers is to hold a single value.

Consider an example case of the `location` header. If one wants to check that
the `location` is `/foo`, the always-array idiom looks like the following:

```php
$location = $response->headers->getHeader('location') ?? [];

if (
    count($location) === 1
    && reset($location) === '/foo'
) {
    // location is /foo
};
```

That is, the consumer cannot be guaranteed that the `location` exists at all
as an array, nor that is has only one value if it does, nor that the only array
key is `0`.

What about an always-string idiom? If there were multiple values, they would
have to be concatenated in a comma-separated string (which might not even be a
valid format for the header in question). In turn, that makes it difficult to
iterate over the values without first parsing the returned string into an array,
which is burdensome for consumers.

In contrast, the string-or-array idiom looks like this for single-valued
headers:

```php
$location = $response->headers->getHeader('location');

if ($location === '/foo') {
    // location is /foo
}
```

With this idiom, if `location` has been specified multiple times, the
identicality check is guaranteed to fail (as it should).

Multiple-valued headers are returned as an array of strings, making it trivial
to iterate over them.

Finally, if consumers must allow for multiple values, even when only a single
value might be present, it is easy to cast the `getHeader()` return to an
`(array)` as needed:

```php
$xFooValues = $response->headers->getHeader('x-foo') ?? [];

foreach ((array) $xFooValues as $xFooValue) {
    // ...
}
```

With all that in mind, Response-Interop favors the string-or-array approach.

### Why does _ResponseHeadersCollection_ provide no methods for managing header callbacks?

PHP provides a [`header_register_callback()`][] function to execute callbacks
when headers are sent. None of the researched projects provided any equivalent
affordances.

Implementors desiring something similar are encouraged to add such logic as
necessary, perhaps in the `sendResponse()` logic.

### Why does _ResponseHeadersCollection_ provide cookie affordance methods?

All of the researched projects provide some sort of affordance for cookie
management. Indeed, PHP itself has a [`setcookie()`][] function separate from
the more general [`header()`][] function.

In terms of interface design, the `set-cookie` values are more complex than most
response headers. That difference makes appropriate typehinting more difficult
on methods designed for *both* general headers *and* `set-cookie` headers.

Further, it is useful to be able to find or replace a cookie by its name, and
general-purpose header methods cannot accomplish that.

### Why does _ResponseHeadersCollection_ not provide other affordance methods?

The researched projects included other affordances around specific headers and
behaviors.  For example, many of them afford a `redirect()` mechanism to set
the status code and `location` header at the same time. Others provide
affordances around caching-related headers such as `etag`, `vary`, the
`cache-control` directives, and so on.

However, the choice of affordances and their different implementations varied
too widely to discern any common approaches. As such, Response-Interop does
not specify affordances for other behaviors.

## Appendix: Relevant PHP Functions

- Status line
    - https://php.net/http_response_code

- Headers
    - https://php.net/header
    - https://php.net/header_register_callback
    - https://php.net/header_remove
    - https://php.net/headers_list
    - https://php.net/headers_sent

- Cookies
    - https://php.net/setcookie
    - https://php.net/setrawcookie

* * *

[_Exception_]: https://php.net/Throwable
[_ResponseBodyHandler_]: #responsebodyhandler
[_ResponseBodySenderService_]: #responsebodysenderservice
[_ResponseCookieHelperService_]: #responsecookiehelperservice
[_ResponseHeadersCollection_]: #responseheaderscollection
[_ResponseSenderService_]: #responsesenderservice
[_ResponseStruct_]: #responsestruct
[_ResponseThrowable_]: #responsethrowable
[_ResponseTypeAliases_]: #responsetypealiases
[_Throwable_]: https://php.net/Throwable
[`flush()`]: https://php.net/flush
[`fseek()`]: https://php.net/fseek
[`fwrite()`]: https://php.net/fwrite
[`header_register_callback()`]: https://php.net/header_register_callback
[`rewind()`]: https://php.net/rewind
[`stream_get_contents()`]: https://php.net/stream_get_contents
[`stream_copy_to_stream()`]: https://php.net/stream_copy_to_stream
[`header()`]: https://php.net/header
[`setcookie()`]: https://php.net/setcookie
[`urldecode()`]: https://php.net/urldecode
[`urlencode()`]: https://php.net/urlencode
[BCP 14]: https://www.rfc-editor.org/info/bcp14
[RFC 2119]: https://datatracker.ietf.org/doc/html/rfc2119
[RFC 6265]: https://datatracker.ietf.org/doc/html/rfc6265
[RFC 8174]: https://datatracker.ietf.org/doc/html/rfc8174
