<?php namespace Scriptotek\OaiPmh;

/**
 * Thrown when we receive an error from the server indicating a bad request
 * http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
 */
class BadRequestError extends \Exception
{

    public function __construct($message = null, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}