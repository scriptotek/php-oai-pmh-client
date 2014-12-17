<?php namespace Scriptotek\Oai;

/**
 * Only to be thrown after a given number of retries
 */
class ResponseException extends \Exception
{

    public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}