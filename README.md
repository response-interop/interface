# Response-Interop Interface Package

This package provides interoperable interfaces to encapsulate, buffer, and send
server-side response values in PHP 8.4 or later, in order to reduce the global
mutable state and inspection problems that exist with the PHP response-sending
functions. It reflects, resolves, and refines the common practices of over a
dozen different userland projects.

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED",  "MAY", and "OPTIONAL" in this document are to be
interpreted as described in [BCP 14][] ([RFC 2119][], [RFC 8174][]).

## Interfaces

This package defines the following interfaces:

- [_ResponseStruct_][] encapsulates the server response status line, headers,
  and body.

- [_ResponseStatusLineStruct_][] encapsulates status line for the response,
  including the HTTP version and the status code.

- [_ResponseHeadersCollection_][] encapsulates the headers for the response,
  including affordances for cookie management.

- [_ResponseBodyContent_][] affords management of non-string,
  resource-intensive, or response-modifying content.

- [_ResponseCookieHelperService_][] affords conversion of cookie representations
  to and from strings and arrays.

Response-Interop also defines a marker interface, [_ResponseThrowable_][], for
marking an [_Exception_][] as response-related.

Finally, Response-Interop defines a [_ResponseTypeAliases_][] interface with
PHPStan types to aid static analysis.

Notes:

- **Response-Interop interfaces are mutable.** None of the researched projects
  afforded readonly or immutable response objects.

- **Whereas PHP sends-as-it-goes, Response-Interop collects-then-sends.**
  For example, the PHP sends a header at the moment the [`header()`][] function
  is called. In contrast, Response-Interop buffers the header specifications,
  and sends them only when the `sendResponse()` method is called.

### _ResponseStruct_

The [_ResponseStruct_][] interface encapsulates the server response. It
is composed of these public properties and method:

- ```php
  public ResponseStatusLineStruct $statusLine { get; set; }
  ```
    - The status line for the response.

- ```php
  public ResponseHeadersCollection $headers { get; set; }
  ```
    - The headers for the response, including affordances for cookie management.

- ```php
  public string|Stringable|ResponseBodyContent $body { get; set; }
  ```
    - The body for the response.
    - Notes:
        - **The `$body` may be a string, a _Stringable_ object, or some other
          content source.** The single most common kind of body content is an
          in-memory string. However, there are other common kinds of content,
          such as when sending a large file for download, at which point a
          _ResponseBodyContent_ instance affords improved resource management
          and response modification.

- ```php
  public function sendResponse() : void
  ```
    - Sends the response.
    - If the `$body` is an instance of _ResponseBodyContent_, implementations
      MUST call its `prepareResponse()` method before sending anything.
    - Implementations MAY check to see if the response can be sent; when doing
      so, implementations MUST throw a _ResponseThrowable_ if the response
      cannot be sent.

### _ResponseStatusLineStruct_

The [_ResponseStatusLineStruct_][] interface encapsulates the status line for
the server response. It is composed of these public properties and method:

- ```php
  public response_http_version_string $httpVersion { get; set; }
  ```
    - The HTTP version string for the response; e.g. `'1.1'`.
    - Implementations MAY validate this value; implementations doing so MUST
      throw a _ResponseThrowable_ on invalidity.

- ```php
  public response_status_code_int $statusCode { get; set; }
  ```
    - The status code for the response; e.g. `200`.
    - Implementations MAY validate this value; implementations doing so MUST
      throw a _ResponseThrowable_ on invalidity.

- ```php
  public function sendResponseStatusLine() : void
  ```
    - Sends the status line for the response.

### _ResponseHeadersCollection_

The [_ResponseHeadersCollection_] interface encapsulates the headers for the
response, including affordances for cookie management.

Implementations MUST normalize each `response_header_field_string` argument
to lower case.

Implementations MUST validate each `response_header_field_string` argument,
and MUST throw a _ResponseThrowable_ on invalidity.

Implementations MUST throw a _ResponseThrowable_ if a
`response_header_value_string` argument is blank.

Implementations MAY validate other method arguments; when doing so,
implementations MUST throw a _ResponseThrowable_ on invalidity.

Notes:

- **Header fields are retained in lower case.** This standardizes
  expectations around header field lookups.

- **Header fields must be valid.** In general, this means the header field
  must match the regular expression `/^:?[a-z][a-z0-9-]+$/`.

- **Header values cannot be blank.** If `trim($value) === ''` then the
  `$value` is blank.

[_ResponseHeadersCollection_][] is composed of the following methods:

- ```php
  public function setHeader(
      response_header_field_string $field,
      response_header_value_string $value
  ) : void
  ```
    - Sets the `$value` for a header, replacing all existing `$value`s for that
      header.
    - If the normalized `$field` is `set-cookie`, implementations MUST retain
      the `$value` such that the cookie can be retrieved by name (e.g. via
      `getCookieAsArray()` or `getCookieAsString()`); if the cookie cannot be
      retained in such a way, implementations MUST throw a _ResponseThrowable_.

- ```php
  public function addHeader(
      response_header_field_string $field,
      response_header_value_string $value
  ) : void
  ```
    - Adds a `$value` for a header, keeping all previous `$value`s for that
      header.
    - If there are no existing `$value`s for the header, implementations
      MUST behave as if `setHeader()` was called with the same `$field` and
      `$value`.
    - Implementations MUST retain each added `$value` separately from all
      previous `$value`s.
    - If the normalized `$field` is `set-cookie`, implementations MUST retain
      the `$value` such that the cookie can be retrieved by name (e.g. via
      `getCookieAsArray()` or `getCookieAsString()`); if the cookie cannot be
      retained in such a way, implementations MUST throw a _ResponseThrowable_.

- ```php
  public function hasHeader(response_header_field_string $field) : bool
  ```
    - Reports if a header exists.

- ```php
  public function getHeader(
      response_header_field_string $field
  ) : null|response_header_value_string|array<response_header_value_string>
  ```
    - Returns the `$value`(s) for a header.
    - Implementations MUST return `null` if there is no `$value` for the
      header.
    - Implementations MUST use a string if there is only one `$value` for
      the header.
    - Implementations MUST use an array of strings if there is more than one
      `$value` for the header.
    - Notes:
        - **This method returns a string if there is only one value.** This is
          to support the most common  case for most response headers; i.e., a single value.
          This reduces the occurrence of the idiom `getHeader('field-name')[0]`.
          If consumers require the return to be an array regardless of the number
          of values, they may cast the return to `(array)`.
        - **Cookies are always returned as strings.** This is to keep the return
          types consistent for all headers, such that the returned values can be
          used directly in [`header()`][] calls if needed. In practical terms,
          the implementation should use `getCookiesAsStrings()` as the source
          for `set-cookie` values.

- ```php
  public function unsetHeader(response_header_field_string $field) : void
  ```
    - Removes a header entirely.

- ```php
  public function hasHeaders() : bool
  ```
    - Reports if any headers exist.

- ```php
  public function getHeaders() : array
  ```
    - Returns an array of all `$value`s of all headers, keyed by the header
      field.
    - Implementations MUST use a string if there is only one `$value` for a
      header.
    - Implementations MUST use an array of strings if there is more than one
      `$value` for a header.
    - Notes:
        - **Cookies are always returned as strings.** This is to keep the return
          types consistent for all headers, such that the returned values can be
          used directly in [`header()`][] calls if needed. In practical terms,
          the implementation should use `getCookiesAsStrings()` as the source
          for `set-cookie` values.

- ```php
  public function unsetHeaders() : void
  ```
    - Removes all headers.

- ```php
  public function setCookie(
      response_cookie_name_string $name,
      response_cookie_value_string $value,
      response_cookie_attributes_array $attributes = []
  ) : void
  ```
    - Sets a named cookie as a `response_cookie_array`, replacing any
      existing cookie of the same name.
    - Implementations MUST retain the cookie such that it can be retrieved by
      name  (e.g. via `getCookieAsArray()` or `getCookieAsString()`).
    - Implementations MUST NOT encode the arguments.

- ```php
  public function hasCookie(response_cookie_name_string $name) : bool
  ```
    - Reports if a named cookie exists.

- ```php
  public function getCookieAsArray(
      response_cookie_name_string $name
  ) : ?response_cookie_array
  ```
    - Returns a named cookie as a `response_cookie_array`, or `null` if it does
      not exist.
    - Implementations retaining the cookie as a `response_header_value_string`
      MUST convert it to a `response_cookie_array` via the
      [_ResponseCookieHelperService_][] method `parseResponseCookieString()`.

- ```php
  public function getCookieAsString(string $name) : ?string;
  ```
    - Returns a named cookie as a string suitable for use as a header value, or
      or `null` if it does not exist.
    - Implementations retaining the cookie as a `response_cookie_array`
      MUST convert it to a `response_header_value_string` via the
      [_ResponseCookieHelperService_][] method `composeResponseCookieString()`.

- ```php
  public function unsetCookie(response_cookie_name_string $name) : void
  ```
    - Removes a named cookie.
    - Notes:
        - **This is not the same as deleting a cookie from the browser.** To do
            that, consumers need to send a named cookie with an expiration date
            in the past.

- ```php
  public function hasCookies() : bool
  ```
    - Reports if any cookies exist.

- ```php
  public function getCookiesAsArrays() : array<
      response_cookie_name_string,
      response_cookie_array
    >
  ```
    - Returns all cookies as an array where each key is the cookie name and
      each value is its `response_cookie_array`.
    - Implementations retaining a cookie as a `response_header_value_string`
      MUST represent that cookie as if it had been retrieved via the
      `getCookieAsArray()` method.

- ```php
  public function getCookiesAsStrings() : array<
      response_cookie_name_string,
      response_header_value_string
  >
  ```
    - Returns all cookies as an array where each key is the cookie name and
      each value is its `response_header_value_string`.
    - Implementations retaining a cookie as a `response_cookie_array` MUST
      represent that cookie as if it had been retrieved via the
      `getCookieAsString()` method.

- ```php
  public function unsetCookies() : void
  ```
    - Removes the `set-cookie` header entirely.
    - Notes:
        - **This is not the same as deleting all cookies from the browser.** To
          do that, consumers need to send named cookies with expiration dates in
          the past.

- ```php
  public function sendResponseHeaders() : void
  ```
    - Sends all headers.
    - Implementations SHOULD send header fields in lower case, but MAY send
      header fields in some other RFC-approved case.

### _ResponseBodyContent_

The [_ResponseBodyContent_][] inteface affords management and sending of
non-string, resource-intensive, or response-modifying content.

Notes:

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

[_ResponseBodyContent_][] is composed of the following methods:

- ```php
   public function prepareResponse(ResponseStruct $response) : void
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
  public function sendResponseBody() : void
  ```
    - Sends the body content.
    - Notes:
        - **Sending logic is content- and implementation-specific.** Different
          kinds of content require different sending mechanisms. Some kinds may
          be amenable to a simple `echo`, others may require specific encoding,
          and yet others may require more involved resource or stream handling.

### _ResponseCookieHelperService_

Response-Interop affords representing `set-cookie` header values in two
ways:

- as a `response_header_value_string`, for working with header strings
  directly; and,
- as a `response_cookie_array`, for working with the `set-cookie` components
  more conveniently.

This interface provides the functionality needed to convert back and forth
between the two representations, using these two methods:

- ```php
  public function parseResponseCookieString(
      response_header_value_string $setCookieString
  ) : ?response_cookie_array;
  ```
    - Parses a `response_header_value_string` into a `response_cookie_array`.
    - Implementations MUST use a parsing algorithm equivalent to the one in
      [RFC 6265][] section 5.2.
    - Implementations MAY ignore the attribute-specific parsing and validating
      algorithms in [RFC 6265][] sections 5.2.1 et al.
    - Implementations MAY validate parsed attributes; implementations doing so
      MUST treat invalid attributes as missing.
    - Implementations MUST return `null` when the parsed `<name-value-pair>`
      lacks a `%x3D` (`=`) character, or when the parsed cookie name is empty.
    - Implementations MUST NOT decode the parsed cookie name, value, or
      attributes.
    - Implementations MUST normalize parsed attribute names to lower case.
    - Implementations MUST represent the value of attributes specified without
      `=<attribute-value>` as boolean `true`.
    - Notes:
        - **These directives are specific but non-restrictive.** For example,
          cookie attributes other than the ones found in [RFC 6265][] may be parsed
          and captured into the `response_cookie_array`, such as `SameSite` and
          `Partitioned`.

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
      response_cookie_array $cookie
  ) : response_header_value_string;
  ```
    - Composes a `response_cookie_array` into a `response_header_value_string`.
    - Implementations MUST NOT encode the cookie name, value, or attributes.
    - Implementations SHOULD use lower case for attribute names but MAY use any
      other case approved in the relvant RFCs.
    - Implementations MUST omit `=<attribute-value>` when the attribute value
      is boolean `true`.
    - Notes:
        - **These directives are specific but non-restrictive.** For example,
        cookie attributes other than the ones found in [RFC 6265][] may be
        composed into the `response_header_value_string`, such as `SameSite`
        and `Partitioned`.

### _ResponseThrowable_

The [_ResponseThrowable_][] interface extends [_Throwable_][] to mark an
[_Exception_] as response-related. It adds no class members.

### _ResponseTypeAliases_

The [_ResponseTypeAliases_][] interface defines these PHPStan type aliases to
aid static analysis:

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

- `response_http_version_string`
    - A `string` used for specifying an HTTP version.

- `response_status_code_int`
    - An `int` specifying an HTTP response code.

## Implementations

Implementations MAY define additional class members not defined in these
interfaces.

Notes:

- **Reference implementations** may be found at <https://github.com/response-interop/impl>.

## Q & A

### Why are there only mutable (as vs. immutable or readonly) interfaces?

None of the researched projects model their response objects as immutable or
readonly.

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

On further inspection, the projects that allow for a reason phrase sometimes set
it with the status code, and sometimes set it separately. This makes it
difficult to resolve the differences between the projects. Given that the HTTP
specifications indicate reason phrases are optional, Response-Interop does not
attempt to resolve those differences.

Implementors desiring a reason phrase are encouraged to add one approriate for
the status code, perhaps in their `sendResponse()` logic.

### Why not put the _ResponseHeadersCollection_ methods directly on the _ResponseStruct_?

Research revealed that separate header and/or cookie collections are used in 6
of the 13 projects. Thus, while not the majority design choice, delegating these
methods to a separate object is common enough to warrant consideration.

With that in mind, Response-Interop finds that the segregation of status line,
headers, and body into their own properties appropriately separates the concerns
around building a response.

### Why does _ResponseHeadersCollection_ allow `string` but not _Stringable_ `$value` types?

None of the researched projects do so. Further, doing so adds complexity to the
implemetation directives on how to retain such values, as well to the return
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

With all that in mind, Response-Interop favors of the string-or-array approach.

### Why does _ResponseHeadersCollection_ provide no methods for managing header callbacks?

PHP provides a [`header_register_callback()`][] function to execute callbacks
when headers are sent. None the researched projects provided any equivalent
affordances.

Implementors desiring something similar are encouraged to add such logic as
necessary, perhaps in the `sendResponseHeaders()` logic.

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
    - http://php.net/http_response_code

- Headers
    - http://php.net/header
    - http://php.net/header_register_callback
    - http://php.net/header_remove
    - http://php.net/headers_list
    - http://php.net/headers_sent

- Cookies
    - http://php.net/setcookie
    - http://php.net/setrawcookie

* * *

[_Exception_]: https://php.net/Throwable
[_ResponseBodyContent_]: #responsebodycontent
[_ResponseCookieHelperService_]: #responsecookiehelperservice
[_ResponseHeadersCollection_]: #responseheaderscollection
[_ResponseStatusLineStruct_]: #responsestatuslinestruct
[_ResponseStruct_]: #responsestruct
[_ResponseThrowable_]: #responsethrowable
[_ResponseTypeAliases_]: #responsetypealiases
[_Throwable_]: https://php.net/Throwable
[`header()`]: https://php.net/header
[`header_register_callback()`]: https://php.net/header_register_callback
[`setcookie()`]: https://php.net/setcookie
[BCP 14]: https://www.rfc-editor.org/info/bcp14
[RFC 2119]: https://www.rfc-editor.org/rfc/rfc2119.txt
[RFC 6265]: https://datatracker.ietf.org/doc/html/rfc6265
[RFC 8174]: https://www.rfc-editor.org/rfc/rfc8174.txt
