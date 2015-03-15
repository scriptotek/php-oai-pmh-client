<?php namespace Scriptotek\OaiPmh;

/**
 * Thrown after a configurable number of retries
 */
class ConnectionError extends \Exception
{

    public function __construct($message = null, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}