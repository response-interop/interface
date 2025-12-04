# Change Log

## 1.0.0-alpha1

Incoporated changes indicated by private review:

- Renamed _ResponseBodyContent_ to _ResponseBodyHandler_.

- Cookie names and values must be decoded/encoded appropriately.

- Modified header field string validation notes.

- Extracted _ResponseSenderService_
    - Removed `ResponseStruct::sendResponse()`
    - Removed `ResponseHeadersCollection::sendResponseHeaders()`
    - Removed _ResponseStatusLineStruct_, condensing its properties into _ResponseStruct_
    - Pass _ResponseSenderService_ to `ResponseBodyHandler::sendResponseBody()`
    - Advise against `echo` (etc.) in favor of calling _ResponseSenderService_ body-sending methods

- Improved language consistency with other *-interops

## 1.0.0-dev1

First release for private review.

