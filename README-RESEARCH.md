# Research

Response-Interop is based on research including the following projects, which model their server-side response objects on buffered versions of PHP functions:

- [aura/web](https://github.com/auraphp/Aura.Web/blob/2.x/src/Response.php) (aura)
- Cake 2 _CakeResponse_ (cake2)
- Code Igniter 3 _CI_Output_ (ci3)
- [flightphp/core](https://github.com/flightphp/core/blob/master/flight/net/Response.php) (flight)
- [horde/controller](https://github.com/horde/Controller/blob/horde_controller2/lib/Horde/Controller/Response.php) (horde)
- [Klein](https://github.com/klein/klein.php/blob/master/src/Klein/AbstractResponse.php) (klein)
- [Phalcon HTTP Request](https://github.com/phalcon/cphalcon/blob/v3.4.0/phalcon/http/response.zep) (phalcon)
- [Slim 2](https://github.com/slimphp/Slim/blob/2.x/Slim/Http/Response.php) (slim2)
- [symfony/http-foundation](https://github.com/symfony/http-foundation/blob/6023ec7607254c87c5e69fb3558255aca440d72b/Response.php) (symfony)
- [tempestphp/tempest-framework](https://github.com/tempestphp/tempest-framework/blob/main/packages/http/src/IsResponse.php) (tempest)
- [YAF](https://www.php.net/manual/en/class.yaf-response-abstract.php) (see also the [C code](https://github.com/laruence/yaf/blob/master/responses/yaf_response_http.c)) (yaf)
- [yiisoft/yii2-dev](https://github.com/yiisoft/yii2/blob/5fb3f809c59f742537df77e0da1ad36a1175834a/framework/web/Response.php) (yii2)
- [Zend Framework 1](https://github.com/zendframework/zf1/blob/master/library/Zend/Controller/Response/Abstract.php) (zf1)

The following projects were considered but eventually excluded because they attempt to model their request objects on HTTP messages instead of on the PHP superglobals:

- Joomla uses PSR-7 for responses (though not for requests).
- Lithium [_lithium\\action\\Response_](https://github.com/UnionOfRAD/lithium/blob/1.3/action/Request.php)
- PSR-7 [_Psr\\Http\\Message\\ResponseInterface_](https://github.com/php-fig/http-message/blob/master/src/ResponseInterface.php)
- Zend Framework 2 [_Zend\\Http\\PhpEnvironment\\Request_](https://github.com/zendframework/zendframework/blob/release-2.4/library/Zend/Http/PhpEnvironment/Response.php)

> (N.b.: The response objects in PSR-7 and ZF2 are remarkably similar to each other.)

The following projects were considered but eventually excluded because they send-as-they-go, instead of buffering values for later sending:

- MediaWiki [WebResponse](https://github.com/wikimedia/mediawiki/blob/master/includes/Request/WebResponse.php)
- Nette [Response](https://github.com/nette/http/blob/master/src/Http/Response.php)

See also <https://docs.google.com/spreadsheets/d/e/2PACX-1vQzJP00bOAMYGSVQ8QIIJkXVdAg-OMEfkgna7-b2IsuoWN8x_TazxEYn-yVDF2XQIqnzmHqdDO3KEKx/pubhtml> for an earlier version of this research.

## Mutability

All of the researched projects use mutable repsonse objects; none of them advertise readonly or immutable behaviors.

## Status Line

### HTTP Version

|           | Set                                               | Get                            | Notes                              |
| --------- | ------------------------------------------------- | ------------------------------ | ---------------------------------- |
| aura      | $status->setVersion(string $version) : void       | $status->getVersion() : string | Aura\Web\Response\Status $status   |
| cake2     | protocol(string $protocol) : string               | protocol() : string            | -                                  |
| ci3       | -                                                 | -                              | -                                  |
| flight    | -                                                 | -                              | uses $_SERVER['REQUEST_PROTOCOL']  |
| horde     | -                                                 | -                              | -                                  |
| klein     | protocolVersion(string $protocol_version) : $this | protocolVersion() : string     | -                                  |
| phalcon   | -                                                 | -                              | -                                  |
| slim      | -                                                 | -                              | -                                  |
| symfony   | setProtocolVersion(string $version) : $this       | getProtocolVersion() : string  | -                                  |
| tempest   | -                                                 | -                              | -                                  |
| yaf       | -                                                 | -                              | -                                  |
| yii2      | string $version                                   | string $version                | -                                  |
| zf1       | -                                                 | -                              | -                                  |

Slim: HTTP protocol version is set as part of config.

### Status Code

|           | Set                                                | Get                                  | Notes                            |
| --------- | -------------------------------------------------- | ------------------------------------ | -------------------------------- |
| aura      | $status->setCode(int $code) : void                 | $staus->getCode() : int              | Aura\Web\Response\Status $status |
| cake2     | statusCode(int $code) : int                        | statusCode() : int                   | -                                |
| ci3       | set_status_header($code = 200, $text = '') : $this | -                                    | -                                |
| flight    | status(int $code) : $this                          | status() : int                       | -                                |
| horde     | -                                                  | -                                    | -                                |
| klein     | code(int $code) : $this                            | code() : int                         | -                                |
| phalcon   | setStatusCode(int $code, string $message) : $this  | getStatusCode() : ?int               | -                                |
| slim      | setStatus(int $status) : void                      | getStatus() : int                    | -                                |
| symfony   | setStatusCode(int $code, string $text) : $this     | getStatusCode() : int                | -                                |
| tempest   | setStatus(Status $status) : $this                  | $status                              | Tempest\Http\Status $status      |
| yaf       | setHeader(string $protocol, string $statusReason)  | getHeader(string $protocol) : string | -                                |
| yii2      | setStatusCode($value, $text = null) : $this        | getStatusCode() : int                | -                                |
| zf1       | setHttpResponseCode(int $code) : $this             | setHttpResponseCode() : int          | -                                |

YAF and Yii2 set the status and reason phrase together.

### Reason Phrase

|           | Set                                                      | Get                                  | Notes                            |
| --------- | -------------------------------------------------------- | ------------------------------------ | -------------------------------- |
| aura      | $status->setPhrase(string $phrase) : void                | $status->getPhrase() : string        | Aura\Web\Response\Status $status |
| cake2     | -                                                        | -                                    | -                                |
| ci3       | -                                                        | -                                    | -                                |
| flight    | -                                                        | -                                    | set automatically by status()    |
| horde     | -                                                        | -                                    | -                                |
| klein     | status()->setMessage(string $message) : $this            | status()->getMessage() : string      | status() : Klein\HttpStatus      |
| phalcon   | setStatusCode(int $code, string $message) : $this        | getReasonPhrase() : string           | -                                |
| slim      | -                                                        | -                                    | -                                |
| symfony   | setStatusCode(int $code, string $text) : $this           | -                                    | -                                |
| tempest   | -                                                        | -                                    | -                                |
| yaf       | setHeader(string $protocol, string $statusReason) : void | getHeader(string $protocol) : string | -                                |
| yii2      | setStatusCode($value, $text = null) : $this              | string $statusText                   | -                                |
| zf1       | -                                                        | -                                    | -                                |

YAF and Yii2 set the status and reason phrase together.

Cake2 sets the reason phrase from the status code internally.

### Status Code Mappings to Reason Phrases

When provided, they are public properties or constants.

|           | Code-to-Phrase Mapping Via ...                                |
| --------- | ------------------------------------------------------------- |
| aura      | -                                                             |
| cake2     | -                                                             |
| ci3       | -                                                             |
| flight    | public static array $codes [int code => string phrase]        |
| horde     | -                                                             |
| klein     | -                                                             |
| phalcon   | -                                                             |
| slim      | -                                                             |
| symfony   | public const HTTP_{REASON_PHRASE} = {int status code}         |
| tempest   | enum Status {REASON_PHRASE} = {int status code}               |
| yaf       | -                                                             |
| yii2      | public static array $httpStatuses [int code => string phrase] |
| zf1       | -                                                             |

## Headers

### Set/Add/Unset

|           | Set                                                            | Add                                                                   | Unset                                      | Notes                                                       |
| --------- | -----------------------------------------------------------    | -------------------------------------------------------------         | ------------------------------------------ | ----------------------------------------------------------- |
| aura      | $headers->set(string $label, $value) : void                    | $headers->add(string $label, $value) : void                           | -                                          | Aura\Web\Response\Headers $headers                          |
| cake2     | header(string $header, string $value) : array                  | -                                                                     | -                                          | -                                                           |
| ci3       | set_header(string $header, true) : $this                       | set_header(string $header, false) : $this                             | -                                          | -                                                           |
| flight    | setHeader(string $name, string $value) : $this                 | -                                                                     | setHeader(string $name, null) : $this      | -                                                           |
| horde     | setHeader(string $name, string $value) : void                  | -                                                                     | -                                          | -                                                           |
| klein     | header(string $key, string $value) : $this                     | -                                                                     | headers()->remove(string $key) : void      | headers() : Klein\DataCollection\HeaderDataCollection       |
| phalcon   | setHeader(string $name, string $value) : $this                 | -                                                                     | removeHeader(string $name) : $this         | -                                                           |
| slim      | $headers->set(string $key, string $value) : void               | -                                                                     | -                                          | Slim\Helper\Set $headers                                    |
| symfony   | $headers->set(string $key, string\|array\|null $values) : void | $headers->set(string $key, string\|array\|null $values, false) : void | $headers->remove(string $key) : void       | Symfony\Component\HttpFoundation\ResponseHeaderBag $headers |
| tempest   | -                                                              | addHeader(string $key, string $value) : $this                         | removeHeader(string $key) : $this          | -                                                           |
| yaf       | setHeader(string $name, string $value, true) : bool            | setHeader(string $name, string $value, false) : bool                  | clearHeaders(): void                       | -                                                           |
| yii2      | getHeaders()->set(string $name, string $value = '') : $this    | getHeaders()->add(string $name, string $value) : $this                | getHeaders()->remove(string $name) : $this | getHeaders() : yii\web\HeaderCollection                     |
| zf1       | setHeader(string $name, string $value, true) : $this           | setHeader(string $name, string $value, false) : $this                 | clearHeader(string $name) : $this          | -                                                           |

- "Unset" is for an explicit method to unset; in some projects, setting a header to an empty value removes it.
- Horde affords setting all headers as once: `setHeaders(array $headers) : void`
- Yaf only removes all headers.
- These projects use an external object for headers: aura, slim, symfony, yii2.

### Get/Has

|           | Get One                                                                        | Get All                         | Has                                             | Notes                                                       |
| --------- | ----------------------------------------------------------------------------   | ------------------------------- | ----------------------------------------------- | ----------------------------------------------------------- |
| aura      | $headers->get(string $label) : string|string[]                                 | $headers->get() : array         | -                                               | Aura\Web\Response\Headers $headers                          |
| cake2     | -                                                                              | header() : array                | -                                               | -                                                           |
| ci3       | get_header(string $header) : string                                            | array $headers                  | -                                               | -                                                           |
| flight    | get(string $name) : ?string                                                    | getHeaders() : array            | -                                               | -                                                           |
| horde     | -                                                                              | getHeaders() : array            | -                                               | -                                                           |
| klein     | headers()->get(string $key) : string                                           | headers()->all() : string[]     | headers()->exists(string $key) : bool           | headers() : Klein\DataCollection\HeaderDataCollection       |
| phalcon   | getHeaders()->get(string $name) : string                                       | getHeaders()->toArray() : array | getHeaders()->has(string $name) : bool          | getHeaders() : Phalcon\Http\Response\Headers                |
| slim      | $headers->get(string $key) : string                                            | $headers->all()                 | $headers->has(string $key) : bool               | Slim\Helper\Set $headers                                    |
| symfony   | $headers->get(string $key) : ?string                                           | $headers->all()                 | $headers->has(string $key) : bool               | Symfony\Component\HttpFoundation\ResponseHeaderBag $headers |
| tempest   | getHeader(string $name): ?Header                                               | array $headers                  | -                                               | -                                                           |
| yaf       | getHeader(string $name): ?string                                               | -                               | -                                               | -                                                           |
| yii2      | getHeaders()->get($name, $default = null, $first = true) : string\|array\|null | getHeaders()->toArray() : array | getHeaders()->offsetExists(string $name) : bool | getHeaders() : yii\web\HeaderCollection                     |
| zf1       | -                                                                              | getHeaders() : array            | -                                               | -                                                           |

- has() is for an explicit method to see if a header is set, presume getting a header also tells if it is set.

external object: aura, phalcon, slim, symfony, yii2

TBD: how is each headers stored internally: as an array, or as a string?

### Independent Sending (Public)

|           | Send Headers          | Headers Sent?        | Notes |
| --------- | --------------------- | -------------------- | ----- |
| aura      | -                     | -                    | -     |
| cake2     | -                     | -                    | -     |
| ci3       | -                     | -                    | -     |
| flight    | sendHeaders() : $this | headersSent() : bool | -     |
| horde     | -                     | -                    | -     |
| klein     | sendHeaders() : $this | -                    | -     |
| phalcon   | sendHeaders() : $this | -                    | -     |
| slim      | -                     | -                    | -     |
| symfony   | sendHeaders() : $this | -                    | -     |
| tempest   | -                     | -                    | -     |
| yaf       | -                     | -                    | -     |
| yii2      | -                     | -                    | -     |
| zf1       | sendHeaders() : $this | -                    | -     |

## Cookies

### Set/Raw/Unset

|           | Set                                                     | Raw | Unset                                               | Notes                                                          |
| --------- | ------------------------------------------------------- | --- | --------------------------------------------------  | -------------------------------------------------------------- |
| aura      | $cookies->set(...) : void                               | -   | -                                                   | Aura\Web\Response\Cookies $cookies                             |
| cake2     | cookie([...]) : array                                   | -   | -                                                   | -                                                              |
| ci3       | -                                                       | -   | -                                                   | -                                                              |
| flight    | -                                                       | -   | -                                                   | -                                                              |
| horde     | -                                                       | -   | -                                                   | -                                                              |
| klein     | cookie(...) : $this                                     | -   | cookies()->remove() :                               | cookies() : Klein\DataCollection\ResponseCookiesDataCollection |
| phalcon   | getCookies()->set(...) : $this                          | -   | getCookies()->delete(string $name) : $this          | getCookies() : Phalcon\Http\Response\Cookies                   |
| slim      | $cookies->set($name, [...]) : void                      | -   | $cookies->unset(string $name) : void                | Slim\Helper\Set $cookies                                       |
| symfony   | $headers->setCookie(Cookie $cookie) : void              | -   | $headers->clearCookie(...) : void                   | Symfony\Component\HttpFoundation\ResponseHeaderBag $headers    |
| tempest   | addCookie(Cookie $cookie): self                         | -   | removeCookie(string $key) : $this                   | -                                                              |
| yaf       | -                                                       | -   | -                                                   | -                                                              |
| yii2      | getCookies()->add(Cookie $cookie) : void                | -   | getCookies()->remove(Cookie\|string $cookie) : void | getCookies() : yii\web\CookieCollection                        |
| zf1       | setRawHeader(Zend_Http_Header_SetCookie $value) : $this | -   | -                                                   | -                                                              |

`...` indicates the native setcookie() parameters

`[...]` indicates key-value pairs corresponding to the native setcookie() parameters

klein: cf ResponseCookie and ResponseCookieDataCollection

CI3: Cookies are set through the CI_Input class

external object: aura, phalcon, slim, symfony, yii2, CI3

### Get/Has

|           | Get One                                    | Get All                          | Has                                             | Notes                                                          |
| --------- | --------------------------------           | -------------------------------- | ----------------------------------------------- | -------------------------------------------------------------- |
| aura      | $cookies->get(string $name = null) : array | $cookies->get() : array          | -                                               | Aura\Web\Response\Cookies $cookies                             |
| cake2     | cookie(string $cookie) : ?array            | cookie() : array                 | -                                               | -                                                              |
| ci3       | -                                          | -                                | -                                               | -                                                              |
| flight    | -                                          | -                                | -                                               | -                                                              |
| horde     | -                                          | -                                | -                                               | -                                                              |
| klein     | cookies()->get(string $key)                | cookies()->all() : array         | cookies()->exists(string $key) : bool           | cookies() : Klein\DataCollection\ResponseCookiesDataCollection |
| phalcon   | getCookies()->get(string $name) : Cookie   | getCookies()->toArray() : array  | getCookies()->has(string $name) : bool          | getCookies() : Phalcon\Http\Response\Cookies                   |
| slim      | $cookies->get(string $key)                 | $cookies->all()                  | $cookies->has(string $key) : bool               | Slim\Helper\Set $cookies                                       |
| symfony   | -                                          | $headers->getCookies()           | -                                               | Symfony\Component\HttpFoundation\ResponseHeaderBag $headers    |
| tempest   | $cookieManager->get(string $key) : ?Cookie | $cookieManager->all() : Cookie[] | -                                               | Tempest\Http\Cookie\CookieManager $cookies                     |
| yaf       | -                                          | -                                | -                                               | -                                                              |
| yii2      | getCookies()->get(string $name) : ?Cookie  | getCookies()->toArray() : array  | getCookies()->offsetExists(string $name) : bool | getCookies() : yii\web\CookieCollection                        |
| zf1       | -                                          | -                                | -                                               | -                                                              |

CI3: Cookies are retrieved through the CI_Input class

external object: aura, klein, phalcon, slim, symfony, tempest, yii2

### Independent Sending (Public)

|           | Send Cookies                | Notes |
| --------- | --------------------------- | ----- |
| aura      | -                           | -     |
| cake2     | -                           | -     |
| ci3       | -                           | -     |
| flight    | -                           | -     |
| horde     | -                           | -     |
| klein     | sendCookies() : $this       | -     |
| phalcon   | sendCookies() : $this       | -     |
| slim      | -                           | -     |
| symfony   | -                           | -     |
| tempest   | -                           | -     |
| yaf       | -                           | -     |
| yii2      | -                           | -     |
| zf1       | -                           | -     |

Symfony sends cookies as part of sendHeaders().

ZF1 sends cookies as part of sendHeaders().

Otherwise, cookies are sent as part of the response-as-a-whole sending process.

## Body Content

### Set/Add/Unset

|           | Set                                                        | Add                                                       | Unset                           | Notes                            |
| --------- | ------------------------------------------------------     | --------------------------------------------------------- | ------------------------------- | -------------------------------- |
| aura      | $content->set(mixed $content) : void                       | -                                                         | -                               | Aura\Web\Response\Content object |
| cake2     | body(string $content) : string                             | -                                                         | -                               | -                                |
| ci3       | set_output(string $output) : $this                         | append_output(string $output) : $this                     | -                               | -                                |
| flight    | write(string $str, true) : $this                           | write(string $str) : $this                                | clearBody() : $this             | -                                |
| horde     | setBody(resource\|string $body)                            | -                                                         | -                               | -                                |
| klein     | body(string $body) : $this                                 | append(string $content) : $this                           | -                               | -                                |
| phalcon   | setContent(string $content) : $this                        | appendContent(string $content) : $this                    | -                               | -                                |
| slim2     | setBody(string $body) : void                               | write(string $body) : string                              | -                               | -                                |
| symfony   | setContent(?string $content) : $this                       | -                                                         | -                               | -                                |
| tempest   | setBody(View\|string\|array\|Generator\|null $body): $this | -                                                         | -                               | -                                |
| yaf       | setBody(string $content, string $key = null) : bool        | -                                                         | -                               | -                                |
| yii2      | ?string $content                                           | -                                                         | -                               | -                                |
| zf1       | setBody(string $content, ?string $name = null) : $this     | appendBody(string $content, ?string $name = null) : $this | clearBody(?string $name = null) | -                                |

add() is to append to content

unset() is to unset the content, presume setting to empty/null is equivalent

Cake: cf. file()

Phalcon: cf setJsonContent(), setFileToSend()

Yii: cf. mixed $data, resource $stream

YAF and ZF1 support segmented bodies.

ZF1: cf. setException()

### Get/Has

|           | Get                                                       | Has | Notes                              |
| --------- | --------------------------------------------------------- | --- | ---------------------------------- |
| aura      | $content->get() : mixed                                   | -   | Aura\Web\Response\Content $content |
| cake2     | body() : string                                           | -   | -                                  |
| ci3       | get_output() : string                                     | -   | -                                  |
| flight    | getBody() : string                                        | -   | -                                  |
| horde     | getBody() : resource\|string                              | -   | -                                  |
| klein     | body() : string                                           | -   | -                                  |
| phalcon   | getContent() : string                                     | -   | -                                  |
| slim2     | getBody() : string                                        | -   | -                                  |
| symfony   | getContent() : string\|false                              | -   | -                                  |
| tempest   | View\|string\|array\|Generator\|null $body                | -   | -                                  |
| yaf       | getBody(?string $key = null) : mixed                      | -   | -                                  |
| yii2      | ?string $content                                          | -   | -                                  |
| zf1       | getBody(bool\|string $spec = false) : string\|array\|null | -   | -                                  |

YAF will return a string or an array.

Yii2: cf. mixed $data, resource $stream

YAF and ZF1 support segmented bodies.

has() is to see if content exists, presume get() as equivalent

### Indepndent Sending (Public)

|           | Send                 | Notes |
| --------- | -------------------  | ----- |
| aura      | -                    | -     |
| cake2     | -                    | -     |
| ci3       | -                    | -     |
| flight    | -                    | -     |
| horde     | -                    | -     |
| klein     | sendBody() : $this   | -     |
| phalcon   | -                    | -     |
| slim2     | -                    | -     |
| symfony   | sendContent() : void | -     |
| tempest   | -                    | -     |
| yaf       | -                    | -     |
| yii2      | sendContent() : void | -     |
| zf1       | outputBody() : void  | -     |

### Content-Sending Logic

Aura:

        $content = $this->response->content->get();

        if (is_callable($content)) {
            echo $content();
        } else {
            echo $content;
        }

Cake2:

		if ($this->_file) {
			$this->_sendFile($this->_file, $this->_fileRange);
			$this->_file = $this->_fileRange = null;
		} else {
			$this->_sendContent($this->_body);
		}

Code Igniter 3:

        echo $output; // Send it to the browser!

Flight:

        echo $this->body;

Horde:

        if (is_resource($body)) {
            stream_copy_to_stream($body, fopen('php://output', 'a'));
        } else {
            echo $body;
        }

Klein:

        echo (string) $this->body;

Phalcon:

		let content = this->_content;
		if content != null {
			echo content;
		} else {
			let file = this->_file;

			if typeof file == "string" && strlen(file) {
				readfile(file);
			}
		}

		let this->_sent = true;
		return this;

Slim:

        //Send body, but only if it isn't a HEAD request
        if (!$this->request->isHead()) {
            echo $body;
        }

Symfony:

        public function sendContent(): static
        {
            echo $this->content;

            return $this;
        }

Tempest:

        if ($response instanceof EventStream) {
            $this->sendEventStream($response);
            return;
        }

        $body = $response->body;

        if ($response instanceof File || $response instanceof Download) {
            readfile($body);
        } elseif (is_array($body) || $body instanceof JsonSerializable) {
            echo json_encode($body);
        } elseif ($body instanceof View) {z
            echo $this->viewRenderer->render($body);
        } else {
            echo $body;
        }

        ob_flush();

YAF:

		ZEND_HASH_FOREACH_VAL(response->body, entry) {
			zend_string *str = zval_get_string(entry);
			php_write(ZSTR_VAL(str), ZSTR_LEN(str));
			zend_string_release(str);
		} ZEND_HASH_FOREACH_END();

Yii2:

        if ($this->stream === null) {
            echo $this->content;

            return;
        }

        // Try to reset time limit for big files
        if (!function_exists('set_time_limit') || !@set_time_limit(0)) {
            Yii::warning('set_time_limit() is not available', __METHOD__);
        }

        if (is_callable($this->stream)) {
            $data = call_user_func($this->stream);
            foreach ($data as $datum) {
                echo $datum;
                flush();
            }
            return;
        }

        $chunkSize = 8 * 1024 * 1024; // 8MB per chunk

        if (is_array($this->stream)) {
            list($handle, $begin, $end) = $this->stream;

            // only seek if stream is seekable
            if ($this->isSeekable($handle)) {
                fseek($handle, $begin);
            }

            while (!feof($handle) && ($pos = ftell($handle)) <= $end) {
                if ($pos + $chunkSize > $end) {
                    $chunkSize = $end - $pos + 1;
                }
                echo fread($handle, $chunkSize);
                flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
            }
            fclose($handle);
        } else {
            while (!feof($this->stream)) {
                echo fread($this->stream, $chunkSize);
                flush();
            }
            fclose($this->stream);
        }

ZF1:

        $body = implode('', $this->_body);
        echo $body;

## Response As A Whole

### Send/Sent

|           | Send                            | Sent            | Sender                               |
| --------- | ------------------------------- | --------------- | ------------------------------------ |
| aura      | __invoke() : void               | -               | Aura\Web\ResponseSender object       |
| cake2     | send() : void                   | -               | self                                 |
| ci3       | _display() : void               | -               | self                                 |
| flight    | send() : void                   | sent() : bool   | self                                 |
| horde     | writeResponse() : void          | -               | Horde_Controller_ResponseWriter_Web  |
| klein     | send() : void                   | isSent() : bool | self                                 |
| phalcon   | send() : void                   | isSent() : bool | self                                 |
| slim2     | run() : void                    | -               | Slim\Slim                            |
| symfony   | send() : $this                  | -               | self                                 |
| tempest   | send(Response $response) : void | -               | Tempest\Router\GenericResponseSender |
| yaf       | response() : void               | -               | self                                 |
| yii2      | send() : void                   | bool $isSent    | self                                 |
| zf1       | sendResponse()                  | -               | self                                 |

Flight: cf. downloadFile()
Klein: cf. chunk(), dump(), file(), json()
Slim: sending happens automatically at the end of $app->run()
Tempest: cf. sendEventStream(EventStream $response): void
Yii: cf. sendFile(), sendStreamAsFile(), sendContentAsFile(), xSendFile()
ZF1: when present, sends exceptions as strings instead of the body

## Header Callbacks

cf. https://www.php.net/header_register_callback

No researched projects are doing this.

## Other Functionality

### Redirect-Related

Aura:
- $redirect->
    - to($location, $code = 302, $phrase = null)
    - afterPost($location)
    - created($location)
    - movedPermanently($location)
    - found($location)
    - seeOther($location)
    - temporaryRedirect($location)
    - permanentRedirect($location)

Cake 2:

- location($url = null)

Horde:

- setRedirectUrl($url)

Klein:

- redirect($url, $code = 302)

Phalcon:

- redirect(location = null, boolean externalRedirect = false, int statusCode = 302)

Slim 2:

- redirect ($url, $status = 302)

YAF:

- setRedirect(string $url)

Yii2:

- redirect($url, $statusCode = 302, $checkAjax = true)

8/13 implement some form of redirection, sometimes with status code (5/8, always "302"), sometimes not (3/8).

Would have to be on the main Response object, e.g. `redirect($location, $code)`.

This is more a convenience function than a core Response function.

### Cache-Related

Cake 2:

- disableCache()
- cache($since, $time = '+1 day')
- sharable($public = null, $time = null)
- sharedMaxAge($seconds = null)
- maxAge($seconds = null)
- mustRevalidate($enable = null)
- expires($time = null)
- modified($time = null)
- notModified()
- vary($cacheVariances = null)
- etag($tag = null, $weak = false)

CI3:

- set_cache_header($last_modified, $expiration)

Flight:

- cache($expires) (sets expires, cach-control, pragma)

Phalcon:

- setExpires(<\DateTime> datetime)
- setLastModified(<\DateTime> datetime)
- setCache(int! minutes)
- setNotModified() // Sends a Not-Modified response
- setEtag(string etag)

Symfony

- setPublic()
- setImmutable(bool $immutable = true)
- mustRevalidate()
- setDate(\DateTimeInterface $date)
- expire()
- setExpires(?\DateTimeInterface $date)
- setMaxAge(int $value)
- setStaleIfError(int $value)
- setStaleWhileRevalidate(int $value)
- setSharedMaxAge(int $value)
- setTtl(int $seconds):
- setClientTtl(int $seconds)
- setLastModified(?\DateTimeInterface $date):
- setEtag(?string $etag, bool $weak = false)
- setCache(array $options)
- setNotModified()
- setVary(string|array $headers, bool $replace = true)

5/13 implement any form of cache functionality. No wide agreement.

Even direct cache-control access is limited (cake & symfony only).

### Content-Related

Aura:
- $content->...
    - setCharset($charset)
    - setType($type)
    - setEncoding($encoding)
    - setDisposition($disposition, $filename = null)

Cake 2:
- type($contentType = null)
- charset($charset = null)
- length($bytes = null)

CI 3:

- set_content_type($mime_type, $charset = NULL)
- get_content_type()

Phalcon

- setContentType(string contentType, charset = null)
- setContentLength(int contentLength)

Symfony

- setCharset(string $charset)

1/13 implement content-disposition.
1/13 implement content-encoding.
2/13 implement content-length.
3/13 implement charset.
4/13 implement content-type.

Just no common agreement here.

* * *

redirect/location: Aura, Cake2, Horde, Klein, Phalcon, Slim2, Yaf, Yii2 (8/13)
- but these are so simple as to not require special treatment ($response->headers->setHeader('location', $url); $response->status_code = 302);

type/charset: aura, cake, ci3, phalcon, symfony (5/13)
- might be worth putting on Body object, though these are pretty straightforward and might be managed by a custom Body object

cache-related: cake (cache-control + others), ci (other), flight (other), phalcon (others), symfony (cache-control) (5/13)
- might be worth a CacheControl object because those are more-complex

and for some of the cache/status stuff, you need the request (e.g. 304 NOT MODIFIED needs If-Modified-Since etc.)

but none of the projects carries a request object. maybe better to pass the request/response through a separate service? or maybe  have responseFor($request) ?
