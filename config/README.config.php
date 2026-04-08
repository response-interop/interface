<?php return [
    'namespace' => 'ResponseInterop\\Interface\\',
    'directory' => dirname(__DIR__) . '/src',
    'template' => dirname(__DIR__) . '/resources/README.tpl.md',
    'interfaces' => [
        'ResponseStruct',
        'ResponseHeadersCollection',
        'ResponseBodyHandler',
        'ResponseCookieHelperService',
        'ResponseSenderService',
        'ResponseBodySenderService',
        'ResponseThrowable',
        'ResponseTypeAliases',
    ],
];
