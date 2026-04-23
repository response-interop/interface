# Change Log

## 1.0.0-beta2

Incorporated clarifications from public and private review:

- Clarified that cookies and `set-cookie` headers are kept synchronized.

- Opened `response_cookie_attributes_array` to permit additional string
  attributes such as `SameSite` and `Partitioned`.

- Documented boolean-flag cookie attributes as specified-by-presence.

- Added throw-on-negative directives for `$length` and `$offset` in
  `ResponseBodySenderService::sendResponseBodyResource()`; clarified
  `$length` of zero means send zero bytes.

- Added a note in `sendResponse()` preferring stream-based output via
  `sendResponseBodyString()` over `echo`/`print`.

- Narrowed `response_cookie_array` `value` type from
  `response_header_value_string` to `response_cookie_value_string`.

- Documentation, typo, and tooling refinements.

## 1.0.0-beta1

Incorporated one change from private review:

- Extracted _ResponseBodySenderService_ from _ResponseBodySender_ to segregate
  the body-sending methods from the main sending method. _ResponseBodyHandler_
  method `sendResponseBody()` now takes _ResponseBodySenderService_ as its
  parameter.

- Updated documentation.

## 1.0.0-alpha1

Incorporated changes indicated by private review:

- Renamed _ResponseBodyContent_ to _ResponseBodyHandler_.

- Cookie names and values must be decoded/encoded appropriately.

- Modified header field string validation notes.

- Extracted _ResponseSenderService_:

    - Removed `ResponseStruct::sendResponse()`
    - Removed `ResponseHeadersCollection::sendResponseHeaders()`
    - Removed _ResponseStatusLineStruct_, condensing its properties into _ResponseStruct_
    - Pass _ResponseSenderService_ to `ResponseBodyHandler::sendResponseBody()`
    - Advise against `echo` (etc.) in favor of calling _ResponseSenderService_ body-sending methods

- Improved language consistency with other *-interops

## 1.0.0-dev1

First release for private review.
