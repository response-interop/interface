<?php
declare(strict_types=1);

namespace ResponseInterop\Interface;

use Throwable;

/**
 * [_ResponseThrowable_][] extends [_Throwable_][] to mark an [_Exception_][] as
 * response-related.
 *
 * It adds no class members.
 */
interface ResponseThrowable extends Throwable
{
}
