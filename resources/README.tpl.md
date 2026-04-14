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

{{= list }}

Notes:

- **Response-Interop interfaces are mutable.** None of the researched projects
  afforded readonly or immutable response objects.

- **Whereas PHP sends-as-it-goes, Response-Interop collects-then-sends.**
  For example, PHP sends a header at the moment the [`header()`][] function
  is called. In contrast, Response-Interop buffers the header specifications,
  and sends them only when the `sendResponse()` method is called.

{{= docs }}

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
[`echo`]: https://php.net/echo
[`flush()`]: https://php.net/flush
[`fseek()`]: https://php.net/fseek
[`fwrite()`]: https://php.net/fwrite
[`header_register_callback()`]: https://php.net/header_register_callback
[`print`]: https://php.net/print
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
[RFC 9110]: https://datatracker.ietf.org/doc/html/rfc9110
[IANA HTTP Status Code Registry]: https://www.iana.org/assignments/http-status-codes
